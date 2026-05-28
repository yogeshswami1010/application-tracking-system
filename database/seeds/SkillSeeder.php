<?php

use Illuminate\Database\Seeder;
use App\Skill;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Skill::create(['name' => 'Angular JS', 'category_id' => 1]);
        Skill::create(['name' => 'Vue.JS', 'category_id' => 1]);
        Skill::create(['name' => 'Laravel 5.4', 'category_id' => 1]);
        Skill::create(['name' => 'English', 'category_id' => 3]);
        Skill::create(['name' => 'Blogging', 'category_id' => 2]);
    }
}
