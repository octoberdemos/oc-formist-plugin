<?php namespace Acme\Formist\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('acme_formist_order_items', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('order_id')->unsigend()->nullable();
            $table->integer('product_id')->unsigned()->nullable();
            $table->decimal('price', 8)->nullable()->default(null);
            $table->integer('qty')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acme_formist_order_items');
    }
}
