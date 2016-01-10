<?php namespace Bm\Field\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTemplatesTable extends Migration
{

    public function up()
    {
        Schema::create('bm_field_templates', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('name');
            $table->text('partial')->nullable();
            $table->text('related')->nullable();
            $table->text('partial_category')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bm_template_field', function($table) {
            $table->engine = 'InnoDB';
            $table->integer('template_id');
            $table->integer('field_id');
            $table->integer('ordering')->nullable();
            $table->primary(['template_id', 'field_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bm_field_templates');
        Schema::dropIfExists('bm_template_field');
    }
}
