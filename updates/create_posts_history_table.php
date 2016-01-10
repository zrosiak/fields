<?php namespace Bm\Field\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePostsHistoryTable extends Migration
{

    public function up()
    {
        Schema::create('rainlab_blog_posts_history', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('post_id');
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->string('title')->nullable();
            $table->string('slug')->index();
            $table->text('excerpt')->nullable();
            $table->text('content')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('published')->default(false);
            $table->text('content_html')->nullable();
            $table->date('expire_at')->nullable();
            $table->jsonb('additional')->nullable();
            $table->integer('template_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->text('url')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rainlab_blog_posts_history');
    }
}
