<?php

use Illuminate\Database\Seeder;
use App\Models\Companies;
use App\Models\Locations;

class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      //delete all existing locations
      Locations::truncate();


      $companies = Companies::all();
      foreach ($companies as $company) {
        $tpl_customer_ids = explode(',', $company->fulfillment_ids);
        if (count($tpl_customer_ids)) {
          foreach ($tpl_customer_ids as $tpl_customer_id) {
            if (!is_numeric($tpl_customer_id) || $tpl_customer_id <= 0)
              continue;

            //see if this tpl customer already has locations setup
            $result = Locations::where('tpl_customer_id', $tpl_customer_id)->get();
            if (count($result) <= 0) {
              //create each of the locations for the tpl customer
              Locations::create([
                'name' => 'Spectrum',
                'tpl_customer_id' => $tpl_customer_id,
                'location_type' => 1
              ]);
              Locations::create([
                'name' => 'Customer',
                'tpl_customer_id' => $tpl_customer_id,
                'location_type' => 2
              ]);
              Locations::create([
                'name' => 'Lab',
                'tpl_customer_id' => $tpl_customer_id,
                'location_type' => 3
              ]);
            }
          }
        }
      }
    }
}
