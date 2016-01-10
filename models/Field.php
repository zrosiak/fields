<?php namespace Bm\Field\Models;

use Model;

/**
 * Field Model
 */
class Field extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bm_field_fields';

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
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [
        'template' => [
            'Bm\Field\Models\Template',
            'table' => 'bm_template_field',
        ]
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public $rules = [
        'name' => ['required', 'regex:/^[a-z0-9\-\_]+$/i'],
        'label' => 'required',
        'code' => 'required',
    ];

    /**
     * values for types dropdowan
     * @param integer $keyValue key
     * @return array
     */
    public function getTypeIdOptions($keyValue = 1)
    {
        return FieldTypes::lists('name', 'id');
    }
}