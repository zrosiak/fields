<?php namespace Bm\Field\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BlogCreatePostsTable extends Migration
{

    public function up()
    {
        if (!Schema::hasTable('rainlab_blog_posts')) {
            Schema::create('rainlab_blog_posts', function($table)
            {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('user_id')->unsigned()->nullable()->index();
                $table->string('title')->nullable();
                $table->string('slug')->index();
                $table->text('excerpt')->nullable();
                $table->text('content')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->boolean('published')->default(false);
                $table->text('content_html')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('rainlab_blog_posts')) {
            Schema::drop('rainlab_blog_posts');
        }
    }

}
