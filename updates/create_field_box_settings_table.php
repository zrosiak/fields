<?php namespace Bm\Field\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Bm\Field\Models\BoxSetting;

class CreateFieldBoxSettingsTable extends Migration
{

    public function up()
    {
        Schema::create('bm_field_box_settings', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('name');
            $table->text('partial');
            $table->timestamps();
        });

        BoxSetting::create([
            'name' => 'Aktualności',
            'partial' => 'news',
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('bm_field_box_settings');
    }
}
