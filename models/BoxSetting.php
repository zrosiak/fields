<?php namespace Bm\Field\Models;

use Model;

/**
 * BoxSettings Model
 */
class BoxSetting extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'bm_field_box_settings';

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
