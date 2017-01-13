<?php namespace Acme\Formist\Models;

use Model;

/**
 * Customer Model
 */
class Customer extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'acme_formist_customers';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    public $hasMany = [
        'orders' => Order::class
    ];
}
