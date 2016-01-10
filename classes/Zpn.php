<?php namespace Bm\Field\Classes;

use Bm\Field\Models\Post;
use Bm\Field\Models\Category;

/**
 * Klasa rozszerzająca plugin Field o rzeczy potrzebne w seriwise ZPN
 *
 * @package bm\field
 * @author ziemowit.rosiak@blueservices.pl
 */

class Zpn
{
    /**
     * Tworzenie struktury związku
     * @param Post $post
     * @param Category $category
     * @param array $params dodatkowe parametry
     * @return Collection
     */
    public static function getStructure(Post $post, Category $category, array $params = [])
    {
        $structure = null;
        $categories = $category->getSubcategories(false, true);

        if ($categories) {
            $structure = Category::orderByRaw('"order" asc NULLS LAST')->find($categories)
                ->filter(function($elem) use ($post) {
                    if (empty($elem->communication) === false) {
                        $post = BlogPost::where('id', $elem->communication)->first();
                        $elem->communication = $post->getPaths();
                    }

                    $elem->posts = BlogPost::getPosts([
                        'categories_id' => [$elem->id],
                        'template_id' => 2,
                        'additional' => [
                            'person_type' => 3,
                        ],
                        'order' => "additional->>'order' NULLS LAST",
                    ]);

                    return $elem->posts->count() > 0;
                });
        }

        return $structure;
    }
}
