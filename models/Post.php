<?php namespace Bm\Field\Models;

use App;
use Str;
use Html;
use Lang;
use Model;
use Markdown;
use ValidationException;
use Backend\Models\User;
use DB;
use Cache;
use Carbon\Carbon;
use Bm\Field\Models\Settings;

class Post extends Model
{
    use \Bm\Field\Traits\FieldsGenerator;
    use \October\Rain\Database\Traits\Validation;

    public $table = 'rainlab_blog_posts';

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['published_at'];
    
    public $belongsTo = [
        'template' => ['Bm\Field\Models\Template'],
        'categories' => ['Bm\Field\Models\Category', 'key' => 'category_id'],
        'user' => ['Backend\Models\User'],
    ];

    public $jsonable = [
        'additional',
    ];

    public $rules = [
        'title' => 'required',
        'slug' => [
            'required',
            'between:3,64',
            'regex:/^([0-9]+[\/\:_\-\*\[\]\+\?\|]*)*[a-z]+[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'
        ],
        'url' => ['unique:rainlab_blog_posts,url'],
        'categories' => 'required',
        'content' => '',
        'published_at' => 'sometimes|date',
        'expire_at' => 'sometimes|date|after:published_at',
    ];

    public $guarded = ['id'];

    /**
     * The attributes on which the post list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = array(
        'title asc' => 'Title (ascending)',
        'title desc' => 'Title (descending)',
        'created_at asc' => 'Created (ascending)',
        'created_at desc' => 'Created (descending)',
        'updated_at asc' => 'Updated (ascending)',
        'updated_at desc' => 'Updated (descending)',
        'published_at asc' => 'Published (ascending)',
        'published_at desc' => 'Published (descending)',
        'random' => 'Random'
    );

    /**
     * Allows filtering for specifc categories
     * @param  Illuminate\Query\Builder  $query      QueryBuilder
     * @param  array                     $categories List of category ids
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterCategories($query, $categories)
    {
        return $query->whereHas('categories', function($q) use ($categories) {
            $q->whereIn('id', $categories);
        });
    }

    /**
     * Used to test if a certain user has permission to edit post,
     * returns TRUE if the user is the owner or has other posts access.
     * @param User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return ($this->user_id == $user->id) || $user->hasAnyAccess(['rainlab.blog.access_other_posts']);
    }

    public function afterFetch()
    {
        // dodawanie pól json
        if (empty($this->template_id) === false) {
            $jsonable = Cache::get("template{$this->template_id}_jsonable", function() {
                $jsonable = [];

                foreach ($this->template->field->toArray() as $key => $value) {
                    $code = \Yaml::parse($value['code']);

                    if (isset($code['jsonable']) && $code['jsonable'] == true) {
                        $jsonable[] = $value['name'];
                    }
                }

                Cache::put("template{$this->template_id}_jsonable", $jsonable, 3);

                return $jsonable;
            });
            
            foreach ($jsonable as $value) {
                $this->jsonable[] = $value;
            }
        }

        if (empty($this->additional) === false) {
            // wstrzykiwanie wartości pól do modelu
            foreach ($this->additional as $key => $value) {
                $this->$key = in_array($key, $this->jsonable)
                    ? json_decode($value)
                    : $value;
            }
        }
    }

    public function beforeValidate()
    {
        $this->getUrl();
    }

    /**
     * Pobieranie niezmienionych atrybutów modelu
     * @return array
     */
    public function getOriginals()
    {
        $original = $this->getOriginal();

        if (
            empty($original)
            && $post = Post::first()
        ) {
            $original = $post->getOriginal();
        }

        return $original;
    }

    public function beforeSave()
    {
        $original = $this->getOriginals();

        if (empty($original) === false) {
            // wydobywanie i zapisywanie dodatkowych parametrów
            $dynamicAttributes = array_except(
                $this->getAttributes(),
                array_keys($original)
            );

            foreach ($dynamicAttributes as $key => $attribute) {
                if (strpos($key, 'additional->>') !== false) {
                    // wywalenie z klucza 'additional->>' tworzonego przez relację
                    // @todo obczaić co tu się dzieje:)
                    $newkey = preg_replace('/additional->>\'?([a-z0-9\-\_]+)\'?/', '$1', $key);
                    $dynamicAttributes[$newkey] = $dynamicAttributes[$key];
                    unset($dynamicAttributes[$key]);
                    unset($this->$newkey);
                }

                unset($this->$key);
            }

            $this->additional = $dynamicAttributes;
        }

        // generwanie atrybutu url
        $this->getUrl();
        $this->user_id = \BackendAuth::getUser()
            ? \BackendAuth::getUser()->id
            : null;
    }

    /**
     * Zapisywanie historii edycji artykułu
     */
    public function afterSave()
    {
        if (Settings::get('history', false) == true && isset($this->id)) {
            $post_data = $this->getAttributes();
            $post_data['post_id'] = $post_data['id'];

            \Bm\Field\Models\PostHistory::create($post_data);
        }
    }

    /**
     * Pobieranie kategorii
     */
    public function listCategories($id = null)
    {
        return Category::listCategories();
    }

    /**
     * Pobieranie listy templateów
     */
    public function listTemplates($id = null)
    {
        return Template::lists('name', 'id');
    }

    /**
     * Pobieranie listy użytkowników
     */
    public function listUsers($id = null)
    {
        return User::orderBy('email')->lists('email', 'id');
    }

    /**
     * Opublikowane artykuły
     */
    public function scopePublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', true)
            ->where('published_at', '<=', 'now()')
            ->where(function ($query) {
                $query->whereNull('expire_at')
                    ->orWhere('expire_at', '>', 'now()');
            });
    }

    public function scopeIsPublished($query)
    {
        return $this->scopePublished($query);
    }

    /**
     * Wygasłe artykuły
     */
    public function scopeExpired($query)
    {
        return $query->where('expire_at', '<=', 'now()');
    }

    /**
     * Zwraca url artykułu
     * @return string
     */
    public function getUrl()
    {
        $category_path = $this->categories ? $this->categories->getPaths() : null;
        $this->url = str_replace('//', '/', $category_path . '/' . $this->slug);

        return $this->url;
    }

    /**
     * Zwraca ścieżkę artykułu
     * @return string
     */
    public function getPath()
    {
        $page_name = empty($this->template->page_name)
            ? 'post'
            : $this->template->page_name;
        $category_slug = $this->categories
            ? $this->categories->slug
            : null;
        $this->path = str_replace(
            "/{$page_name}//",
            "/{$page_name}/1/",
            "/{$page_name}/{$category_slug}/{$this->slug}"
        );

        return $this->path;
    }

    /**
     * Generowanie urli do artykułu
     */
    public function generateUrl()
    {
        $this->getUrl();

        return $this->save();
    }

    /**
     * Generowanie urli do artykułów
     */
    public function getPaths($url = true)
    {
        if (!$this->url) {
            $this->generateUrl();
        }

        $this->getPath();

        return $url === true ? $this->url : $this->path;
    }

    /**
     * Generowanie urli do artykułów w kolejce
     * @return void
     */
    public static function queueUrls(array $ids)
    {
        \Queue::push(function($job) use ($ids) {
            Post::find($ids)->each(function($post) {
                $post->generateUrl();
            });

            $job->delete();
        });
    }

    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeCategory($query, $parent_slug = '')
    {
        return $query->whereHas('categories', function($query) use ($parent_slug) {
            $query->where('slug', $parent_slug);
        });
    }

    public function scopeHasView($query)
    {
        return $query->whereHas('template', function($query) {
            $query->whereNotNull('partial');
            $query->where('partial', '<>', '');
        });
    }

    /**
     * Rozbija url na części
     * @param string $url
     * @return array
     *         page numer strony
     *         post slug posta
     *         category slug kategorii
     *         url adres bez numeru strony
     */
    public static function getUrlParts($url)
    {
        $url_parts = array_reverse(array_filter(explode('/', $url)));
        $get_part = function($check = true) use (&$url_parts) {
            return !isset($url_parts[0]) || !($check ?: preg_match('/^[0-9]+?$/', $url_parts[0]))
                ? null
                : array_shift($url_parts);
        };
        $page_number = $get_part(false);
        $url = preg_replace("/\/$page_number$/", '', $url);
        $post_url = $get_part();
        $category_url = $get_part();

        return [
            'page' => $page_number,
            'post' => $post_url,
            'category' => $category_url,
            'url' => $url,
        ];
    }

    /**
     * Sprawdzanie urla z routingu i zwracanie ścieżki do pliku
     */
    public static function checkUrl($url)
    {
        $url_parts = Post::getUrlParts($url);

        if ($post = Post::published()->where('url', $url_parts['url'])->first()) {
            $url = $post->getPaths(false);
        } elseif ($category = Category::where('url', $url_parts['url'])->whereNotNull('template_id')->first()) {
            $url = $category->getPaths(false) . ($url_parts['page'] ? '/' . $url_parts['page'] : '');
        }

        return $url;
    }

    /**
     * scope pól dodatkowych
     * @param $query
     * @param array $additional para klucz wartość pola dodatkowego
     * @return
     */
    public function scopeAdditional($query, array $additional)
    {
        foreach ($additional as $key => $value) {
            $query->where(Db::raw("additional->>'$key'"), $value);
        }

        return $query;
    }

    /**
     * pobieranie listy postów wg parametrów
     * @param array $params parametry
     * @return Collection
     */
    public static function getPosts(Array $params, $query = null)
    {
        extract(
            array_merge([
                'post_id' => null,
                'category' => null,
                'categories_id' => [],
                'template_id' => null,
                'additional' => [],
                'limit' => 0,
                'tags' => false,
                'post_tags' => [],
                'promoted' => false,
                'random' => false,
                'subcategories' => false,
                'category' => null,
                'order' => 'published_at DESC',
                'pagination' => false,
                'posts_per_page' => 10,
                'page' => 1,
            ], $params)
        );

        $query = $query ?: Post::published()->with('template');

        // post_id
        if ($post_id) {
            $query->where('id', '<>', $post_id);
        }

        // categories_id
        if ($categories_id) {
            $categories_id = is_array($categories_id) ? $categories_id : explode(',', $categories_id);
            $category = Category::find($categories_id);
        } elseif (!$category instanceof Category) {
            // category
            $category = Category::find($category);
        }

        // subcategories
        if ($subcategories && $category) {
            if ($category instanceof Category) {
                $categories_id = $category->getSubcategoriesId();
            } else {
                $categories_id = [];

                foreach ($category as $c) {
                    $categories_id = $categories_id + $c->getSubcategoriesId();
                }
            }
        }

        // template_id
        if ($template_id) {
            $query->whereIn('template_id', is_array($template_id) ? $template_id : explode(',', $template_id));
        }

        // random
        if ($random) {
            $query->orderByRaw("RANDOM()");
        } else {
            $query->orderByRaw($order);
        }

        // additional
        if ($additional) {
            $additional = is_array($additional) ? $additional : [$additional];
            $key = array_search('variable', $additional);

            if ($key && isset(${$key})) {
                $additional[$key] = ${$key};
            }

            $query->additional($additional);
        }

        // promoted
        if ($promoted) {
            $query->additional(['is_promoted' => '1']);
        }

        // limit
        if ($limit) {
            $query->limit($limit);
        }

        $query->where(function($q) use (
            $tags,
            $post_tags,
            $categories_id,
            $category
        ) {
            // tags
            if ($tags && $post_tags) {
                $q->whereHas('tags', function($q) use ($post_tags) {
                    $q->whereIn('id', array_fetch($post_tags->toArray(), 'id'));
                });
            }

            // categories
            if ($categories_id) {
                $q->whereIn('category_id', $categories_id);
            }

            // collection
            if (isset($category->collection)) {
                $collection = is_array($category->collection)
                    ? $category->collection
                    : [$category->collection];
                $q->orWhereIn(
                    Db::raw("additional->>'collection'"),
                    array_map('strval', $collection)
                );
            }
        });

        $posts = $pagination
            ? $query->paginate($posts_per_page, $page)
            : $query->get();

        return $posts;
    }

    /**
     * Pobieranie kolejnego posta
     * @param string $order sortowanie
     * @param string $sorting kolejność
     * @return Post
     */
    public function next($order = 'published_at', $sorting = 'asc')
    {
        return static::published()
            ->where('category_id', $this->category_id)
            ->where($order, ($sorting === 'asc' ? '>' : '<'), $this->{$order})
            ->orderBy($order, $sorting)
            ->first();
    }

    /**
     * Pobieranie poprzedniego posta
     * @param string $order sortowanie
     * @param string $sorting kolejność
     * @return Post
     */
    public function previous($order = 'published_at', $sorting = 'desc')
    {
        return $this->next($order, $sorting);
    }
}
