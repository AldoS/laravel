<?php

use Illuminate\Database\Seeder;
use App\Property;

class PropertiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Property::truncate();

        $faker = \Faker\Factory::create();

        // And now, let's create a few articles in our database:
        for ($i = 0; $i < 50; $i++) {
            Property::create([
                'name' => $faker->company,
                'address' => $faker->address,
                'description' => $faker->sentence,
                'imageUrl' => '',
            ]);
        }
    }
}
