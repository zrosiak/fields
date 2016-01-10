<?php namespace Bm\Field\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * ScoreTable Form Widget
 */
class ScoreTable extends FormWidgetBase
{

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'bm_field_score_table';

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

        return $this->makePartial('scoretable');
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addCss('css/scoretable.css', 'Bm.Field');
        $this->addJs('js/scoretable.js', 'Bm.Field');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $value = $this->getLoadValue() ?: null;
        $this->vars['name'] = $this->formField->getName();
        $this->vars['columns'] = $this->formField->config['columns'];
        $this->vars['rows'] = $this->formField->config['rows'];
        $this->vars['value'] = is_array($value)
            ? $value
            : json_decode($value);
    }
}
