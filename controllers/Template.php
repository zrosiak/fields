<?php namespace Bm\Field\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Template Back-end Controller
 */
class Template extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bm.template.access_template'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bm.Field', 'template', 'template');

        $this->addJs('/modules/backend/assets/js/october.treeview.js', 'core');

        $this->bodyClass = '';
    }
}