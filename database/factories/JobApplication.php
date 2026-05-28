<?php

namespace Database\Factories;

use App\JobApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = JobApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = $this->faker;
        return [
            'column_priority' => 0,
            'cover_letter' => $faker->text,
            'email' => $faker->email,
            'full_name' => $faker->name,
            'job_id' => rand(1,3),
            'location_id' => rand(1,3),
            'phone' => $faker->phoneNumber,
            'status_id' => rand(1,5)
        ];
    }
}