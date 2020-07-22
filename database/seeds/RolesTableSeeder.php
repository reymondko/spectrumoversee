<?php

use Illuminate\Database\Seeder;
use App\Models\Roles;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Roles::create([
            'id' => 1,
            'title' => 'Super Admin',
        ]);

        Roles::create([
            'id' => 2,
            'title' => 'Company Admin',
        ]);

        Roles::create([
            'id' => 3,
            'title' => 'Basic User',
        ]);
    }
}
