<?php namespace Bm\Field\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateFieldsTable extends Migration
{

    public function up()
    {
        Schema::create('bm_field_fields', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('name');
            $table->text('label');
            $table->text('code');
            $table->timestamps();
        });

        Schema::table('rainlab_blog_categories', function ($table) {
            $table->integer('parent_id')->nullable();
            $table->integer('post_id')->nullable();
            $table->jsonb('additional')->nullable();
            $table->text('url')->nullable();
            $table->integer('template_id')->nullable();
        });

        Schema::table('rainlab_blog_posts', function ($table) {
            $table->date('expire_at')->nullable();
            $table->jsonb('additional')->nullable();
            $table->integer('template_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->text('url')->nullable();
        });

        if (Schema::hasTable('flynsarmy_menu_menuitems')) {
            Schema::table('flynsarmy_menu_menuitems', function ($table) {
                $table->integer('post_id')->nullable();
                $table->integer('category_id')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('bm_field_fields');

        $columns = [
            'parent_id',
            'post_id',
            'additional',
            'url',
            'template_id',
        ];

        Schema::table('rainlab_blog_categories', function($table) use ($columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn('rainlab_blog_categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
        
        $columns = [
            'expire_at',
            'additional',
            'template_id',
            'category_id',
            'url',
        ];

        Schema::table('rainlab_blog_posts', function($table) use ($columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn('rainlab_blog_posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasTable('flynsarmy_menu_menuitems')) {
            $columns = [
                'post_id',
                'category_id',
            ];

            Schema::table('flynsarmy_menu_menuitems', function($table) use ($columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn('flynsarmy_menu_menuitems', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
