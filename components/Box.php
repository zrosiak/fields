<?php namespace Bm\Field\Components;

use Db;
use Illuminate\Support\Collection;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Bm\Field\Models\Post as BlogPost;
use Bm\Field\Models\Category;
use Bm\Field\Models\Template;
use Bm\Field\Models\BoxSetting;

class Box extends ComponentBase
{
    public $type;
    public $posts;
    public $category;
    public $category_page;
    public $partial_name;
    public $wide;
    public $random;
    public $limit;
    public $subcategories;
    public $is_promoted;
    public $templates;
    public $partial;

    /**
     * {@inheritdoc}
     */
    public function componentDetails()
    {
        return [
            'name'        => 'fieldBox',
            'description' => ''
        ];
    }

    public function defineProperties()
    {
        return [
            'categoryPage' => [
                'title'       => 'bm.field::lang.settings.post_category',
                'description' => 'bm.field::lang.settings.post_category_description',
                'type'        => 'dropdown',
                'default'     => '{{ :categoryPage }}',
                'options'     => Category::lists('name', 'id'),
            ],
            'subcategories' => [
                'title'       => 'Podkategorie',
                'description' => 'Czy brać pod uwagę posty z podkategorii',
                'type'        => 'checkbox',
                'default'     => '0',
                'options'     => [0 => 'Nie', 1 => 'Tak'],
            ],
            'type' => [
                'title'       => 'Typ',
                'description' => 'Typ',
                'type'        => 'dropdown',
                'default'     => '1',
                'options'     => BoxSetting::lists('name', 'id'),
            ],
            'limit' => [
                'title'       => 'Limit',
                'description' => 'Limit',
                'type'        => 'string',
                'default'     => '3',
            ],
            'random' => [
                'title'       => 'Losowy',
                'description' => 'Czy artykuły mają być losowe',
                'type'        => 'checkbox',
                'default'     => '0',
                'options'     => [0 => 'Nie', 1 => 'Tak'],
            ],
            'wide' => [
                'title'       => 'Szersze boksy',
                'description' => 'Szersze boksy',
                'type'        => 'checkbox',
                'default'     => '0',
                'options'     => [0 => 'Nie', 1 => 'Tak'],
            ],
            'template' => [
                'title'       => 'Typ szablonu',
                'description' => 'Typ szablonu artukułów do wyświetlania',
                'type'        => 'string',
                'default'     => '1',
                'validationPattern' => '[0-9]+(,[0-9]+)*'
            ],
            'is_promoted' => [
                'title'       => 'Promowane',
                'description' => 'Wyświetla tylko promowane artykuły',
                'type'        => 'checkbox',
                'default'     => '0',
                'options'     => [0 => 'Nie', 1 => 'Tak'],
            ],
            /*'is_collection' => [
                'title'       => 'Artykuły powiązane',
                'description' => 'Wyświetla również artykuły powiązane z kategorią',
                'type'        => 'checkbox',
                'default'     => '0',
                'options'     => [0 => 'Nie', 1 => 'Tak'],
            ],*/
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function onRun()
    {
        $this->category_page = $this->property('categoryPage');
        $this->wide = $this->property('wide') == true;
        $this->type = $this->property('type');
        $this->limit = $this->property('limit');
        $this->subcategories = $this->property('subcategories');
        $this->random = $this->property('random');
        $this->is_promoted = $this->property('is_promoted');
        $this->templates = $this->property('template');
        $this->partial = BoxSetting::find($this->type);
        $this->category = Category::loadCategory($this->category_page);
        $this->posts = BlogPost::getPosts([
            'post_id' => isset($this->page['post'])
                ? $this->page['post']->id
                : null,
            'category' => $this->category,
            'categories_id' => $this->property('categories_id'),
            'limit' => $this->limit,
            'template_id' => $this->templates,
            'promoted' => $this->is_promoted,
            'random' => $this->random,
            'subcategories' => $this->subcategories,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function onRender()
    {
        $this->page['partial_name'] = isset($this->partial->partial)
            ? $this->partial->partial
            : null;
        $this->page['posts'] = $this->posts;
        $this->page['category'] = $this->category;
        $this->page['random'] = $this->random;
        $this->page['category_page'] = $this->category_page;
        $this->page['wide'] = $this->wide;
    }
}
