<?php

use Illuminate\Database\Seeder;
use App\Models\Shipments;
use App\Models\DeliveryAddress;
use App\Models\ShipperAddress;
use App\Models\Companies;
use Faker\Factory;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = Companies::get();
        $faker = Faker\Factory::create();
        $limit = 100;
        
        foreach($companies as $c){
            for($ctr = 0;$ctr < $limit;$ctr++){

                $name = explode(' ',$faker->name);

                $deliveryAddress = DeliveryAddress::Create([
                    'companies_id' => $c->id,
                    'FirstName' => $name[0],
                    'LastName' => $name[1],
                    'Address1' => $faker->streetAddress,
                    'City' => $faker->city,
                    'State' => $faker->state,
                    'Country' => $faker->country,
                    'PostalCode' => $faker->postcode,
                    'PhoneNumber' => $faker->phoneNumber,
                ]);

                $shipperAddress = ShipperAddress::Create([
                    'companies_id' => $c->id,
                    'FirstName' => $name[0],
                    'LastName' => $name[1],
                    'Address1' => $faker->streetAddress,
                    'City' => $faker->city,
                    'State' => $faker->state,
                    'Country' => $faker->country,
                    'PostalCode' => $faker->postcode,
                    'PhoneNumber' => $faker->phoneNumber,
                ]);

                Shipments::Create([
                    'companies_id' => $c->id,
                    'delivery_address_id' => $deliveryAddress->id,
                    'shipper_address_id' => $shipperAddress->id,
                    'weight' => $faker->randomFloat(3,1, 1000).' kg',
                    'package_type' => $faker->numerify('Package ###'),
                    'insurance_amount' => '$'.$faker->numberBetween(100,1000000)
                ]);
            }
        }
    }
}
