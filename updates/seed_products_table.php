<?php namespace Acme\Formist\Updates;

use Faker\Factory;
use Acme\Formist\Models\Product;
use October\Rain\Database\Updates\Seeder;

class SeedProductsTable extends Seeder
{

    public function run()
    {
        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            Product::create([
                'name' => $faker->words(rand(1,3), true),
                'description' => $faker->paragraph(3),
                'price' => $faker->randomFloat(2, 10, 20)
            ]);
        }
    }

}
