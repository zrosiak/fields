<?php namespace Bm\Field\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Bm\Field\Models\Post;

/**
 * Dropdown Form Widget
 */
class Dropdown extends FormWidgetBase
{

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'bm_field_post_dropdown';

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

        return $this->makePartial('dropdown');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['field'] = $this->formField;
        $this->vars['groups'] = $this->config->groups;
        $this->vars['value'] = $this->getLoadValue() ?: null;
    }

    public function getElems($category_id)
    {
        return Post::remember(5)->where('category_id', $category_id)
            ->orderBy('title')
            ->lists('title', 'id');
    }
}
