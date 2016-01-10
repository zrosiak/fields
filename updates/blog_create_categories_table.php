<?php namespace Bm\Field\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BlogCreateCategoriesTable extends Migration
{

    public function up()
    {
        if (!Schema::hasTable('rainlab_blog_categories')) {
            Schema::create('rainlab_blog_categories', function($table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('slug')->nullable()->index();
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rainlab_blog_posts_categories')) {
            Schema::create('rainlab_blog_posts_categories', function($table) {
                $table->engine = 'InnoDB';
                $table->integer('post_id')->unsigned();
                $table->integer('category_id')->unsigned();
                $table->primary(['post_id', 'category_id']);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('rainlab_blog_categories')) {
            Schema::drop('rainlab_blog_categories');
        }

        if (Schema::hasTable('rainlab_blog_posts_categories')) {
            Schema::drop('rainlab_blog_posts_categories');
        }
    }

}
