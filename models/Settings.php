<?php namespace Bm\Field\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'bm_field_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';
}
