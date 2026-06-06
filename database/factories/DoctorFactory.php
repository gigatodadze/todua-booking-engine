<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'firstname' => fake('ka_GE')->firstName(),
            'lastname' => fake('ka_GE')->lastName(),
        ];
    }
}
