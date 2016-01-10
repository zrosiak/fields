<?php namespace Bm\Field\Tests\Models;

use Bm\Field\Models\Post;
use PluginTestCase;

class PostTest extends PluginTestCase
{
    //protected $refreshPlugins = ['Bm.Field'];

    public function setUp()
    {
        parent::setUp();

        Post::create([
            'title' => 'Testowy artykuł',
            'slug' => 'testowy-artykul',
            'category_id' => 1,
            'additional' => ["test" => true],
        ]);

        $this->post = Post::first();
    }

    public function testCreateFirstPost()
    {
        $this->assertEquals(1, $this->post->id);
    }

    public function testPostHasAdditionalValue()
    {
        $this->assertTrue($this->post->test);
    }

    public function testPostHasUrl()
    {
        $this->assertEquals('/testowy-artykul', $this->post->url);
    }

    public function testIsPublished()
    {
        $this->assertEquals(0, Post::published()->get()->count());
    }

    public function testHasPublished()
    {
        $post = Post::first();
        $post->is_published = true;
        $post->published_at = date('Y-m-d');
        $post->save();
dd(Post::first()->published_at);
        $this->assertEquals(1, Post::published()->get()->count());
    }
}
