<?php namespace Bm\Field\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateTemplatesTablePageName extends Migration
{

    public function up()
    {
        if (Schema::hasTable('bm_field_templates')) {
            Schema::table('bm_field_templates', function ($table) {
                $table->text('page_name')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('bm_field_templates')) {
            Schema::table('bm_field_templates', function($table) {
                $table->dropColumn('page_name');
            });
        }
    }
}
