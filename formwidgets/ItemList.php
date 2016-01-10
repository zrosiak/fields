<?php namespace Bm\Field\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Bm\Field\Models\Field;
use Bm\Field\Models\Template;
use Bm\Field\Models\TemplateField;

/**
 * ItemList Form Widget
 */
class ItemList extends FormWidgetBase
{

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'bm_field_item_list';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('itemlist');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $fields = [];

        if ($this->model->id) {
            $template = Template::find($this->model->id);

            if ($template->field->count()) {
                $fields = $template->field()
                    ->orderBy('bm_template_field.ordering')
                    ->get()
                    ->transform(function($item) {
                        return $item->id;
                    })
                    ->toArray();
            }
        }
        
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = Field::whereNotIn('id', $fields)->orderBy('label')->get();
        $this->vars['field'] = Field::whereIn('id', $fields)->get();
        $this->vars['fields'] = $fields;
        $this->vars['model'] = $this->model;
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addCss('css/itemlist.css', 'Bm.Field');
        $this->addJs('js/itemlist.js', 'Bm.Field');
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        return $value;
    }

    public function onRemove()
    {
        $id = post('id', 0);

        $template = Template::find($this->model->id)->field()->detach($id);
        $this->prepareVars();

        return [
            '#reorderRecords' => $this->makePartial('item_records', ['records' => $this->vars['value']])
        ];
    }

    public function onMove()
    {
        $id = post('id', 0);
        $order = post('order', null);

        if (!$item = Field::find($id)) {
            throw new Exception('Item not found.');
        }

        if (!Template::find($this->model->id)->field->find($id)) {
            Template::find($this->model->id)->field()->attach($item->id);
        }
    }
}
