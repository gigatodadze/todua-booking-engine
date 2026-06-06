<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * @return array|mixed[]
     */
    public function definition(): array
    {
        return [
            'firstname' => fake('ka_GE')->firstName(),
            'lastname' => fake('ka_GE')->lastName(),
            'phone' => '5' . fake()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'id_number' => fake()->numerify('###########'),
        ];
    }
}
