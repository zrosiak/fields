<?php namespace Bm\Field\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateTemplatesTablePageName extends Migration
{

    public function up()
    {
        if (Schema::hasTable('rainlab_blog_categories')) {
            Schema::table('rainlab_blog_categories', function ($table) {
                $table->text('page_name')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('rainlab_blog_categories')) {
            Schema::table('rainlab_blog_categories', function($table) {
                $table->dropColumn('page_name');
            });
        }
    }
}
