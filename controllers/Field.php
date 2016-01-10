<?php namespace Bm\Field\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Field Back-end Controller
 */
class Field extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bm.template.access_field'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bm.Field', 'template', 'field');
    }
}