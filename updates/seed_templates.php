<?php namespace Bm\Field\Updates;

use October\Rain\Database\Updates\Seeder;
use Bm\Field\Models\Template;
use Bm\Field\Models\Field;
use Bm\Field\Models\Category;

class SeedAllTables extends Seeder
{

    public function run()
    {
        /*Category::create([
            'name' => trans('bm.field::lang.categories.uncategorized'),
            'slug' => '',
            'url' => '/',
        ]);*/

        $excerpt = Field::create([
            'name' => 'excerpt',
            'label' => 'Wstęp',
            'code' => "type: textarea\n"
                . "tab: bm.field::lang.post.tab_edit\n"
                . "label: bm.field::lang.post.excerpt\n"
                . "size: small\n",
        ]);
        $content = Field::create([
            'name' => 'content',
            'label' => 'Treść',
            'code' => "type: richeditor\n"
                . "tab: bm.field::lang.post.tab_edit\n"
                . "stretch: true\n"
                . "cssClass: field-slim\n"
                . "language: markdown\n"
                . "showGutter: false\n"
                . "wrapWords: true\n"
                . "fontSize: 13\n"
                . "margin: 15\n",
        ]);
        Field::create([
            'name' => 'category',
            'label' => 'Kategoria',
            'code' => "type: dropdown\n"
                . "options: listCategories\n"
                . "keyFrom: pivot\n"
                . "tab: bm.field::lang.post.tab_related\n",
        ]);
        Field::create([
            'name' => 'photo',
            'label' => 'Zdjęcie',
            'code' => "type: mediafinderplus\n"
                . "tab: bm.field::lang.post.tab_related\n"
                . "mode: image\n"
                . "fileTypes: jpeg,jpg,png,gif\n",
        ]);
        Field::create([
            'name' => 'is_promoted',
            'label' => 'Promowany',
            'code' => "type: balloon-selector\n"
                . "options:\n"
                . " - 'Nie'\n"
                . " - 'Tak'\n"
                . "default: false\n"
                . "span: left\n"
                . "tab: bm.field::lang.post.tab_settings\n",
        ]);
        Field::create([
            'name' => 'download',
            'label' => 'Pliki do pobrania',
            'code' => "type: repeater\n"
                . "tab: bm.field::lang.post.tab_related\n",
        ]);
        Field::create([
            'name' => 'email',
            'label' => 'Adres email',
            'code' => "type: text"
                . "tab: bm.field::lang.post.tab_address\n"
                . "validator: 'email'\n"
                . "placeholder: 'adres@email.pl'\n",
        ]);
        Field::create([
            'name' => 'www',
            'label' => 'Adres www',
            'code' => "type: text\n"
                . "tab: bm.field::lang.post.tab_address\n"
                . "validator: 'sometimes|url'\n"
                . "placeholder: 'http://www.adres.pl'\n",
        ]);
        Field::create([
            'name' => 'tagbox',
            'label' => 'Tagi',
            'code' => "type: owl-tagbox\n"
                . "tab: bm.field::lang.post.tab_edit\n"
                . "slugify: true\n"
                . "placeholder: ''\n"
                . "comment: 'Tagi zatwierdza się przyciskiem TAB'\n",
        ]);

        $template = Template::create([
            'name' => trans('bm.field::lang.templates.article'),
            'partial' => '',
        ]);
        $template->field()->attach($excerpt);
        $template->field()->attach($content);
    }
}
