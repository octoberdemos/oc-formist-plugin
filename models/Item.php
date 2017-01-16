<?php namespace Acme\Formist\Models;

use Model;

/**
 * Item Model
 */
class Item extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'acme_formist_order_items';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'product' => [
            Product::class
        ]
    ];

    public function filterFields($fields, $context = null)
    {
        if ($this->product) {
            $fields->price->value = $this->product->price;
        }
    }

}
