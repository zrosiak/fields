<?php namespace Bm\Field\Components;

use Cache;
use Yaml;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Bm\Field\Models\Post as BlogPost;
use Bm\Field\Models\Category;

class Post extends ComponentBase
{
    /**
     * @var Bm\Field\\Models\Post The post model used for display.
     */
    public $post;

    /**
     * @var Bm\Field\\Models\Category The category model used for display.
     */
    public $category;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $categoryPage;

    /**
     * {@inheritdoc}
     */
    public function componentDetails()
    {
        return [
            'name'        => 'fieldPost',
            'description' => 'bm.field::lang.settings.post_description'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'bm.field::lang.settings.post_slug',
                'description' => 'bm.field::lang.settings.post_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'categoryPage' => [
                'title'       => 'bm.field::lang.settings.post_category',
                'description' => 'bm.field::lang.settings.post_category_description',
                'default'     => '{{ :category }}',
                'type'        => 'dropdown',
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * {@inheritdoc}
     */
    public function onRun()
    {
        $this->post = $this->property('preview')
            ? $this->loadPreview()
            : $this->loadPost();

        if (!$this->post) {
            return \Response::make($this->controller->run('404'), 404);
        }

        $this->loadRelated();
        $this->setMeta();

        $this->page['categoryPage'] = $this->categoryPage;
        $this->page['category'] = $this->category;
        $this->page['post'] = $this->post;
    }

    /**
     * Ładowanie danych posta z bazy
     * @return Post
     */
    protected function loadPost()
    {
        $this->categoryPage = $this->property('categoryPage');
        $this->category = Category::loadCategory($this->categoryPage);

        $post = BlogPost::published()
            ->with('categories')
            ->hasView()
            ->slug($this->property('slug'))
            ->where('category_id', $this->category->id)
            ->first();
            
        return $post;
    }

    /**
     * Ładowanie podglądu artykułu
     * @return Post
     */
    protected function loadPreview()
    {
        $post = null;

        if (
            $this->property('pageId')
            && Cache::has('preview' . $this->property('pageId'))
        ) {
            $data = Cache::get('preview' . $this->property('pageId'));

            if ($post = BlogPost::find((int)$this->property('pageId'))) {
                foreach ($data as $key => $value) {
                    $post->{$key} = $value;
                }

                $post->categories_id = $this->categoryPage = $post->categories->id;
                $this->category = $post->categories;
            }
        }

        return $post;
    }

    /**
     * Doczytanie powiązanych postów
     * @return void
     */
    protected function loadRelated()
    {
        if (empty($this->post->template->related) === false) {
            $this->post->categories_id = $this->post->categories->getSubcategoriesId();

            foreach (Yaml::parse($this->post->template->related) as $key => $value) {
                // sprawdzanie czy istnieje wymagany atrybut
                if (
                    isset($value['require'])
                    && !$this->post->{$value['require']}
                ) {
                    continue;
                }

                // przypisanie wartości z posta
                if (isset($value['variable']) && isset($this->post->{$value['variable']})) {
                    $value[$value['variable']] = $this->post->{$value['variable']};
                }

                $this->post->{$key} = isset($value['run'])
                    ? $this->{$value['run']}($this->post, $this->category, $value)
                    : BlogPost::getPosts([
                        'post_id' => $this->post->id,
                        'categories_id' => empty($this->post->category)
                            ? (
                                empty($this->post->communication->id)
                                    ? $this->post->categories_id
                                    : $this->post->communication->id
                            ) : $this->post->category,
                        'tags' => $this->post->tags,
                    ] + $value);
            }
        }
    }

    /**
     * Ustawoanie meta tagów
     */
    public function setMeta()
    {
        $this->page->title = $this->post->title;
        $this->page->meta_title = empty($this->post->meta_title)
            ? $this->post->title
            : $this->post->meta_title;
        $this->page->meta_description = empty($this->post->meta_description)
            ? $this->post->excerpt
            : $this->post->meta_description;
        $this->page->meta_keywords = empty($this->post->meta_keywords)
            ? $this->post->title
            : $this->post->meta_keywords;
        $this->page->menu_url = $this->post->url;
    }
}
