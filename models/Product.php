<?php namespace Acme\Formist\Models;

use Model;

/**
 * Product Model
 */
class Product extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'acme_formist_products';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];
}
