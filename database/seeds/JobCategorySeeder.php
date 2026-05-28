<?php

use Illuminate\Database\Seeder;
use App\JobCategory;

class JobCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = [
            'Sales',
            'Content',
            'Engineering'
        ];

        foreach ($names as $key => $name):
            if(!is_null($name)){
                JobCategory::create(['name' => $name]);
            }
        endforeach;
    }
}
