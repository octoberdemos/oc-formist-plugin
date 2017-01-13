<?php namespace Acme\Formist\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('acme_formist_customers', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('company')->nullable()->default(null);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('street');
            $table->string('house_number');
            $table->string('zipcode');
            $table->string('city');
            $table->string('email');
            $table->string('phone')->nullable()->default(null);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acme_formist_customers');
    }
}
