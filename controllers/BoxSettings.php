<?php namespace Bm\Field\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Box Settings Back-end Controller
 */
class BoxSettings extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bm.Field', 'template', 'boxsettings');
    }
}