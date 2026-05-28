<?php

use Illuminate\Database\Seeder;
use App\JobLocation;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $location = new JobLocation();
        $location->location = 'Jaipur';
        $location->country_id = 99;
        $location->save();

        $location = new JobLocation();
        $location->location = 'Delhi';
        $location->country_id = 99;
        $location->save();

        $location = new JobLocation();
        $location->location = 'Bangalore';
        $location->country_id = 99;
        $location->save();

    }
}
