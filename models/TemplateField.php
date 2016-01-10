<?php namespace Bm\Field\Models;

use Model;

/**
 * TemplateField Model
 */
class TemplateField extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bm_template_field';

    public $timestamps = false;

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
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}