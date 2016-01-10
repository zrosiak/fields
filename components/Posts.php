<?php namespace Bm\Field\Components;

use Db;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Bm\Field\Models\Post as BlogPost;
use Bm\Field\Models\Category as BlogCategory;
use Bm\Field\Models\Template;
use Input;

class Posts extends ComponentBase
{
    /**
     * A collection of posts to display
     * @var Collection
     */
    public $posts;

    /**
     * Parameter to use for the page number
     * @var string
     */
    public $pageParam;

    /**
     * If the post list should be filtered by a category, the model to use.
     * @var Model
     */
    public $category;

    /**
     * Categories id list
     * @var array
     */
    public $categories;

    /**
     * Message to display when there are no messages.
     * @var string
     */
    public $noPostsMessage;

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $postPage;

    /**
     * Reference to the page name for linking to categories.
     * @var string
     */
    public $categoryPage;

    /**
     * If the post list should be ordered by another attribute.
     * @var string
     */
    public $sortOrder;
    
    /**
     * Parameter to use for the pagination on/off
     * @var string
     */
    public $paginationActive;
    
    /**
     * Szablon artykułów
     * @var string
     */
    public $template;

    /**
     * Kategoria nadrzędna
     * @var string
     */
    public $categoryParent;

    /**
     * {@inheritdoc}
     */
    public function componentDetails()
    {
        return [
            'name'        => 'fieldPosts',
            'description' => 'bm.field::lang.settings.posts_description'
        ];
    }

    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'title'       => 'bm.field::lang.settings.posts_pagination',
                'description' => 'bm.field::lang.settings.posts_pagination_description',
                'type'        => 'string',
                'default'     => '{{ :page }}',
            ],
            'postsPerPage' => [
                'title'             => 'bm.field::lang.settings.posts_per_page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'bm.field::lang.settings.posts_per_page_validation',
                'default'           => '10',
            ],
            'noPostsMessage' => [
                'title'        => 'bm.field::lang.settings.posts_no_posts',
                'description'  => 'bm.field::lang.settings.posts_no_posts_description',
                'type'         => 'string',
                'default'      => 'No posts found',
                'showExternalParam' => false
            ],
            'sortOrder' => [
                'title'       => 'bm.field::lang.settings.posts_order',
                'description' => 'bm.field::lang.settings.posts_order_description',
                'type'        => 'dropdown',
                'default'     => 'published_at desc'
            ],
            'categoryPage' => [
                'title'       => 'bm.field::lang.settings.posts_category',
                'description' => 'bm.field::lang.settings.posts_category_description',
                'type'        => 'dropdown',
                'default'     => '{{ :category }}',
                'group'       => 'Links',
                'options'     => BlogCategory::lists('name', 'id'),
            ],
            'categoryParent' => [
                'title'       => 'Kategoria nadrzędna',
                'description' => 'Kategoria nadrzędna',
                'type'        => 'string',
                'default'     => '{{ :parent }}',
                'group'       => 'Links',
            ],
            'subcategories' => [
                'title'       => 'Podkategorie',
                'description' => 'Czy brać pod uwagę posty z podkategorii',
                'type'        => 'checkbox',
                'default'     => '0',
                'options'     => [0 => 'Nie', 1 => 'Tak'],
            ],
            'template' => [
                'title'       => 'Fragment artykułu',
                'description' => 'Który szablon ma być wykorzystany',
                'type'        => 'string',
            ],
            'template_id' => [
                'title'       => 'Typ artykułu',
                'description' => 'Typ artykułu do wyświetlania na liście',
                'type'        => 'string',
            ],
        ];
    }

    public function getSortOrderOptions()
    {
        return BlogPost::$allowedSortingOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function onRun()
    {
        $this->prepareVars();
        /*
         * If the page number is not valid, redirect
         */
        
        if ($this->paginationActive && $pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->posts->lastPage()) && $currentPage > 1) {
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
            }
        }
    }

    protected function prepareVars()
    {
        $this->pageParam = $this->paramName('pageNumber');
        $this->noPostsMessage = $this->property('noPostsMessage');
        $this->postPage = $this->property('postPage');
        $this->categoryPage = $this->property('categoryPage');
        $this->categoryParent = $this->property('categoryParent');
        $this->category = BlogCategory::loadCategory(
            $this->categoryPage,
            $this->categoryParent
        );
        $this->template = $this->property('template') ?: (
            empty($this->category->template->partial_category)
                ? null
                : $this->category->template->partial_category
        );

        if (
            empty($this->category->id)
            || (
                empty($this->category->template->id)
                && empty($this->template->id)
            )
        ) {
            return \Response::make($this->controller->run('404'), 404);
        }

        // paginacja domyślnie włączona; 'false' wyłącza
        $this->paginationActive = $this->category->pagination !== 'false'
            || $this->property('postsPerPage') > 0;
        $this->posts = BlogPost::getPosts([
            'category' => $this->category,
            'categories_id' => $this->property('categories_id'),
            'template_id' => $this->property('template_id')
                ?: $this->category->template_id,
            'pagination' => $this->paginationActive,
            'posts_per_page' => (int)$this->category->pagination > 0
                ? $this->category->pagination
                : $this->property('postsPerPage'),
            'page' => $this->property('pageNumber'),
            'subcategories' => $this->property('subcategories'),
            'order' => "NULLIF(regexp_replace(additional->>'order', E'\\D', '', 'g'), '')::int NULLS LAST, published_at DESC",
        ]);

        $this->setMeta();
    }

    public function onRender()
    {
        $this->page['pageParam'] = $this->pageParam;
        $this->page['noPostsMessage'] = $this->noPostsMessage;
        $this->page['postPage'] = $this->postPage;
        $this->page['categoryPage'] = $this->categoryPage;
        $this->page['categoryParent'] = $this->categoryParent;
        $this->page['category'] = $this->category;
        $this->page['template'] = $this->template;
        $this->page['posts'] = $this->posts;
    }

    /**
     * Ustawianie meta tagów
     */
    public function setMeta()
    {
        $this->page->title = $this->category->name;
        $this->page->meta_title = empty($this->category->meta_title)
            ? $this->category->title
            : $this->category->meta_title;
        $this->page->meta_description = empty($this->category->meta_description)
            ? $this->category->title
            : $this->category->meta_description;
        $this->page->meta_keywords = empty($this->category->meta_keywords)
            ? $this->category->title
            : $this->category->meta_keywords;
        $this->page->menu_url = $this->category->url;
    }
    
    /**
     * Ajax pagination
     * @return array
     */
    public function onLoadPosts()
    {
        $this->prepareVars();
        $is_last = Input::get('page', 1) >= $this->posts->lastPage() ? true : false;
        $template = Input::get('template', $this->template);

        return [
            'is-last' => $is_last,
            'posts' => $this->posts,
            '@.post-load' => $this->renderPartial('fieldPosts::ajax', [
                'posts' => $this->posts,
                'template' => $template
            ])
        ];
    }
}
