<?php namespace Bm\Field\Models;

use Yaml;
use Model;
use Backend;

/**
 * Template Model
 */
class Template extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bm_field_templates';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [
        'post' => [
            'Bm\Field\Models\Post'
        ]
    ];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [
        'field' => [
            'Bm\Field\Models\Field',
            'table' => 'bm_template_field',
            'order' => 'bm_template_field.ordering',
        ],
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function afterFetch()
    {
        // dodanie walidacji do pól
        foreach ($this->field as $key => $value) {
            $field_config = Yaml::parse($value->code);

            if (empty($field_config['validator']) === false) {
                \Bm\Field\Models\Post::extend(function($model) use ($value, $field_config) {
                    $model->rules[$value->name] = $field_config['validator'];
                });
            }
        }
    }

    public function beforeSave()
    {
        unset($this->items);
    }

    public function listPages()
    {
        return \Cms\Classes\Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
}
