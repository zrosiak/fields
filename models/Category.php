<?php namespace Bm\Field\Models;

use Str;
use Model;
use URL;
use Cache;
use Bm\Field\Models\Post;
use October\Rain\Router\Helper as RouterHelper;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

class Category extends Model
{
    use \Bm\Field\Traits\SimpleTree;
    use \October\Rain\Database\Traits\Validation;
    
    public $table = 'rainlab_blog_categories';

    /*
     * Validation
     */
    public $rules = [
        'name' => 'required',
        'slug' => [
            'required',
            'between:3,64',
            'regex:/^([0-9]+[\/\:_\-\*\[\]\+\?\|]*)*[a-z]+[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i',
        ],
        'url' => 'unique:rainlab_blog_categories,url',
        'order' => 'integer|min:0',
    ];

    public $belongsTo = [
        'parents' => ['Bm\Field\Models\Category', 'key' => 'parent_id'],
        'category_post' => ['Bm\Field\Models\Post', 'key' => 'post_id'],
        'template' => ['Bm\Field\Models\Template'],
    ];

    public $hasMany = [
        'posts' => ['Bm\Field\Models\Post', 'order' => 'published_at desc', 'scope' => 'published'],
        'children' => ['Bm\Field\Models\Category', 'key' => 'parent_id'],
    ];

    public $jsonable = ['additional'];

    public function getPostCountAttribute()
    {
        return Post::where('category_id', $this->id)->count();
    }

    public function beforeValidate()
    {
        // Generate a URL slug for this model
        if (!$this->exists && !$this->slug) {
            $this->slug = Str::slug($this->name);
        }
        
        $this->getUrl();
    }

    public function afterDelete()
    {
        
    }

    /**
     * Budowanie drzewa kategorii
     * @var array
     */
    public static function listCategories()
    {
        $categories = Category::orderByRaw('"order" asc NULLS LAST')
            ->listsNested('name', 'id', '-');
        $except = [];

        return array_except($categories, $except);
    }

    /**
     * Pobieranie listy templateów
     */
    public function listTemplates($id = null)
    {
        return Template::orderBy('name')->lists('name', 'id');
    }

    /**
     * Filtrowanie po nazwie kategorii
     */
    public function scopeFilterCategoryName($query, $category)
    {
        return $query->whereHas('categories', function($q) use ($category) {
            $q->where('name', 'ilike', "%{$category}%");
        });
    }

    /**
     * Zwraca url kategorii
     * @return string
     */
    public function getUrl()
    {
        $parent_url = $this->parent ? $this->parent->getPaths() : '';
        // jeśli ustawiony post kategorii to generujemy urla do niego
        $this->url = $this->category_post
            ? $this->category_post->getUrl()
            : str_replace(['""', '//'], ['', '/'], "{$parent_url}/{$this->slug}");

        return $this->url;
    }

    /**
     * Zwraca ścieżkę kategorii
     * @return string
     */
    public function getPath()
    {
        // jeśli ustawiony post kategorii to generujemy ścieżkę do niego
        $parent = empty($this->parents->slug)
            ? 1
            : $this->parents->slug;
        $this->path = $this->category_post
            ? $this->category_post->getPath()
            : "/category/$parent/$this->slug";

        return $this->path;
    }

    /**
     * Generowanie urli do kategorii
     */
    public function generateUrl()
    {
        $this->getUrl();

        return $this->save();
    }

    /**
     * Zwraca ścieżkę i urla kategorii
     * @param boolean $url true - url, false - path
     * @return string
     */
    public function getPaths($url = true)
    {
        if (!$this->url && $this->id !== 1) {
            $this->generateUrl();
        }

        $this->getPath();

        return $url === true ? $this->url : $this->path;
    }

    /**
     * Zwraca podkategorie
     */
    public function getSubcategories($self = true, $direct = false)
    {
        $categories = $this->{$direct ? 'getChildren' : 'getAllChildren'}();

        if ($self === true) {
            $categories->add($this);
        }

        return $categories;
    }

    /**
     * Zwraca id podkategorii
     */
    public function getSubcategoriesId($self = true, $direct = false)
    {
        return array_fetch(
            $this->getSubcategories($self, $direct)->toArray(),
            'id'
        );
    }

    protected static function loadCategory($category_page = null, $parent = null)
    {
        // jeśli brak kategorii, to za kategorie uznajemy rodzica
        if (empty($category_page) && !empty($parent)) {
            $category_page = $parent;
            unset($parent);
        }

        $query = Category::from('rainlab_blog_categories as category');
        $query->where(function($query) use ($category_page) {
            $query->where('category.slug', $category_page)
                ->orWhere('category.id', (int)$category_page);
        });

        // jeśli ustawiony rodzic, to sprawdzamy czy kategoria do niego należy
        if (isset($parent)) {
            $query->join(
                'rainlab_blog_categories as parent',
                'category.parent_id',
                '=',
                'parent.id'
            );
            $query->where(function($query) use ($parent) {
                $query->where('parent.slug', $parent)
                    ->orWhere('parent.id', (int)$parent);
            });
        }

        // ustawianie ścieżek kategorii
        if ($category = $query->first(['category.*'])) {
            $category->getPaths();
        }

        return $category;
    }

    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeCategory($query, $parent_slug = '')
    {
        return $query->whereHas('parents', function($query) use ($parent_slug) {
            $query->where('slug', $parent_slug);
        });
    }

    public function afterFetch()
    {
        if (empty($this->additional) === false) {
            // wstrzykiwanie wartości pól do modelu
            foreach ($this->additional as $key => $value) {
                $this->$key = in_array($key, $this->jsonable)
                    ? json_decode($value)
                    : $value;
            }
        }
    }

    public function beforeSave()
    {
        $object = isset($this->id) ? $this : Category::first();

        if (isset($object->id) && $original = $object->getOriginal()) {
            // wydobywanie i zapisywanie dodatkowych parametrów
            $dynamicAttributes = array_except(
                $this->getAttributes(),
                array_keys($original)
            );

            foreach ($dynamicAttributes as $key => $attribute) {
                unset($this->$key);
            }

            $this->additional = $dynamicAttributes;
        }

        // generwanie atrybutu url
        $this->getUrl();
    }

    public function afterUpdate()
    {
        $original = $this->getOriginal();

        // generowanie url dla podkategorii i artykułów
        if (
            isset($original['slug'])
            && $original['slug'] !== $this->slug
        ) {
            $this->getSubcategories()->each(function($elem) {
                if ($elem->id !== $this->id) {
                    $elem->generateUrl();
                }

                Post::queueUrls(array_fetch($elem->posts->toArray(), 'id'));
            });
        }
    }

    // Ustawianie post id zawsze na int
    public function setPostIdAttribute($value)
    {
        $this->attributes['post_id'] = $value ?: null;
    }

    // Ustawianie template_id zawsze na int
    public function setTemplateIdAttribute($value)
    {
        $this->attributes['template_id'] = $value ?: null;
    }
}
