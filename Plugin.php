<?php namespace Bm\Field;

use Db;
use Backend;
use Controller;
use Event;
use Redirect;
use Validator;
use Carbon\Carbon;
use Backend\Models\User;
use System\Classes\PluginBase;
use Bm\Field\Models\Post;
use Bm\Field\Models\Category;
use Bm\Field\Models\Template;
use Bm\Field\Models\PostHistory;
use Bedard\BlogTags\Models\Tag;

/**
 * Plugin do zarządzania artykułami
 * @package bm\field
 * @author ziemowit.rosiak@blueservices.pl
 */
class Plugin extends PluginBase
{
    /**
     * @var array   Container for tags to be attached
     */
    private $tags = [];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Field',
            'description' => 'Zarządzanie szablonami i artykułami',
            'author'      => 'BM',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerPermissions()
    {
        return [
            // Szablony
            'bm.template.access_template' => [
                'tab' => 'Szablony',
                'label' => 'Zarządzaj szablonami'
            ],
            'bm.template.access_field' => [
                'tab' => 'Szablony',
                'label' => 'Zarządzaj elementami szablonów'
            ],
            'bm.template.access_settings' => [
                'tab' => 'Szablony',
                'label' => 'Ustawienia'
            ],
            'bm.template.access_boxsettings' => [
                'tab' => 'Szablony',
                'label' => 'Ustawienia boksów'
            ],
            // Artykuły
            'bm.field.access_posts' => [
                'tab' => 'Artykuły',
                'label' => 'bm.field::lang.blog.access_posts'
            ],
            'bm.field.access_categories' => [
                'tab' => 'Artykuły',
                'label' => 'bm.field::lang.blog.access_categories'
            ],
            'bm.field.access_other_posts' => [
                'tab' => 'Artykuły',
                'label' => 'bm.field::lang.blog.access_other_posts'
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Szablony',
                'description' => 'Zarządzaj ustawieniami szablonów',
                'category'    => 'Szablony',
                'icon'        => 'icon-pencil-square-o',
                'class'       => 'Bm\Field\Models\Settings',
                'order'       => 500,
                'permissions' => ['bm.template.access_settings']
            ]
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'Bm\Field\FormWidgets\ScoreTable' => [
                'label' => 'ScoreTable',
                'code'  => 'scoretable'
            ],
            'Bm\Field\FormWidgets\Dropdown' => [
                'label' => 'Dropdown',
                'code'  => 'dropdowngroup'
            ],
        ];
    }

    public function registerComponents()
    {
        return [
            'Bm\Field\Components\Post'       => 'fieldPost',
            'Bm\Field\Components\Posts'      => 'fieldPosts',
            'Bm\Field\Components\Categories' => 'fieldCategories',
            'Bm\Field\Components\Box'        => 'fieldBox',
        ];
    }

    public function registerNavigation()
    {
        return [
            'template' => [
                'label'       => 'Szablony',
                'url'         => Backend::url('bm/field/template'),
                'icon'        => 'icon-pencil-square-o',
                'permissions' => ['bm.template.*'],
                'order'       => 500,

                'sideMenu' => [
                    'template' => [
                        'label'       => 'Szablony',
                        'icon'        => 'icon-cubes',
                        'url'         => Backend::url('bm/field/template'),
                        'permissions' => ['bm.template.access_template']
                    ],
                    'field' => [
                        'label'       => 'Komponent',
                        'icon'        => 'icon-cube',
                        'url'         => Backend::url('bm/field/field'),
                        'permissions' => ['bm.template.access_field']
                    ],
                ]
            ],
            'articles' => [
                'label'       => 'bm.field::lang.blog.menu_label',
                'url'         => Backend::url('bm/field/posts'),
                'icon'        => 'icon-pencil',
                'permissions' => ['bm.field.*'],
                'order'       => 500,

                'sideMenu' => [
                    'articles' => [
                        'label'       => 'bm.field::lang.blog.posts',
                        'icon'        => 'icon-copy',
                        'url'         => Backend::url('bm/field/posts'),
                        'permissions' => ['bm.field.access_posts']
                    ],
                    'categories' => [
                        'label'       => 'bm.field::lang.blog.categories',
                        'icon'        => 'icon-list-ul',
                        'url'         => Backend::url('bm/field/categories'),
                        'permissions' => ['bm.field.access_categories']
                    ]
                ]
            ]
        ];
    }

    public function boot()
    {
        $plugin_manager = \System\Classes\PluginManager::instance();

        // Walidacja tablicy
        Validator::extend('arrayed', function($attribute, $value, $params) {
            foreach ($value as $key => $val) {
                $validation = true;

                for ($i = 1; $i < count($params); $i++) {
                    $validation = $validation && Validator::make($val, [$params[$i] => $params[0]])->fails();
                }

                if ($validation === true) {
                    return false;
                }
            }

            return true;
        });

        // Walidacja numeru telefonu
        Validator::extend('phone', function($attribute, $value, $parameters) {
            return preg_match('/^(\+|(00))?(\([0-9]{2}\))?([ -]?[0-9]{2,})+$/i', $value)
                && mb_strlen($value) >= 9
                && mb_strlen($value) <= 18;
        });

        /**
         * Rozdzerzenie menu o prawidłowe urle do artykułów
         */
        if ($plugin_manager->hasPlugin('Flynsarmy.Menu')) {
            \Flynsarmy\Menu\Models\Menuitem::extend(function($model) {
                $model->addDynamicMethod('beforeSave', function() use ($model) {
                    $classes = [
                        'Flynsarmy\Menu\MenuItemTypes\BlogPost' => 'rainlab_blog_posts',
                        'Flynsarmy\Menu\MenuItemTypes\BlogCategory' => 'rainlab_blog_categories',
                    ];
                    $column = ['post_id', 'category_id'];

                    if (array_key_exists(post('master_object_class'), $classes)) {
                        $object = Db::table($classes[post('master_object_class')])
                            ->find($model->master_object_id);
                            
                        if ($object) {
                            $url = $object->url;
                            $model->url = $url;
                            $model->{$column[array_search(post('master_object_class'), $classes)]} = $model->master_object_id;
                            $model->selected_item_id = $model->selected_item_id ?: $url;
                        }
                    }

                    // usunięcie hosta z urla
                    $model->url = str_replace(\URL::to('/'), '', $model->url);
                });
            });
        }

        // Rozszerzenie pluginu Tags
        if ($plugin_manager->hasPlugin('Bedard.BlogTags')) {
            Tag::extend(function($model) {
                $model->belongsToMany = [
                    'posts' => [
                        'Bm\Field\Models\Post',
                        'table' => 'bedard_blogtags_post_tag',
                        'order' => 'published_at desc'
                    ]
                ];
            });

            // Extend the model
            Post::extend(function($model) {
                // Relationship
                $model->belongsToMany['tags'] = [
                    'Bedard\BlogTags\Models\Tag',
                    'table' => 'bedard_blogtags_post_tag',
                    'order' => 'name'
                ];

                // getTagboxAttribute()
                $model->addDynamicMethod('getTaglistAttribute', function() use ($model) {
                    return $model->tags()->lists('name');
                });

                // setTagboxAttribute()
                $model->addDynamicMethod('setTaglistAttribute', function($tags) use ($model) {
                    $this->tags = $tags;
                });
            });

            // Attach tags to model
            Post::saved(function($model) {
                if ($this->tags) {
                    $ids = [];
                    foreach ($this->tags as $name) {
                        $create = Tag::firstOrCreate(['name' => $name]);
                        $ids[] = $create->id;
                    }

                    $model->tags()->sync($ids);
                }
            });
        }

        /**
         * Problem z grupowaniem w zarządzaniu grupami
         * @todo usunąć po poprawce w octoberze
         */
        Backend\Models\UserGroup::extend(function($model) {
            $model->belongsToMany['users_count']  = [
                'Backend\Models\User',
                'table' => 'backend_users_groups',
                'count' => true,
                'key' =>'user_id'
            ];
        });

        /**
         * Rozszerzanie pól bloga
         */
        Event::listen('backend.form.extendFields', function($form) {
            if (
                $form->getController() instanceof \Bm\Field\Controllers\Posts
                && in_array($form->getContext(), ['create', 'update'])
            ) {
                // Nadpisanie pola z tagami
                if (class_exists('\Bedard\BlogTags\Plugin')) {
                    $form->removeField('tagbox');
                }

                // Generowanie pól szablonu
                $form->addTabFields($form->model->generateFields());

                // Ustawianie domyślnej daty publikacji
                $form->data->published_at = $form->data->published_at ?: Carbon::now();
            }
        });

        /**
         * Rozdzerzenie pól menu o id artykułukategorii
         */
        Event::listen('backend.list.extendColumns', function($widget) {
            if ($widget->getController() instanceof \Flynsarmy\Menu\Controllers\Menus) {
                $widget->addColumns([
                    'post_id' => [
                        'hidden' => true,
                    ],
                    'category_id' => [
                        'hidden' => false,
                    ],
                ]);
            }
        });

        // Rejestracja rozszerzeń Twiga
        Event::listen('cms.page.beforeDisplay', function($controller, $url, $page) {
            $twig = $controller->getTwig();
            $twig->addExtension(new \Bm\Field\Classes\TwigExcerpt());
            $twig->addExtension(new \Bm\Field\Classes\TwigThumbnail());
            $twig->addExtension(new \Bm\Field\Classes\TwigPostUrl());

            if ($page) {
                // Ustawianie aktywnego urla w menu
                $page->menu_url = empty($page->url) ? '/' : $page->url;
            }
        });
    }
}
