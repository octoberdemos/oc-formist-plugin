<?php namespace Acme\Formist\Updates;

use Acme\Formist\Models\Customer;
use October\Rain\Database\Updates\Seeder;

class SeedCustomersTable extends Seeder
{

    public function run()
    {
        Customer::create([
            'company' => 'JohnDoe Ltd.',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'street' => 'John-Doe-Street',
            'house_number' => '1',
            'zipcode' => '12345',
            'city' => 'DoeCity',
            'email' => 'john@doe.com',
            'phone' => '0123456789',
        ]);
    }

}
