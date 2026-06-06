<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'კონსულტაცია',
                'რენტგენოგრაფია',
                'ექოსკოპია',
                'მაგნიტურ-რეზონანსული ტომოგრაფია',
                'კომპიუტერული ტომოგრაფია',
                'ელექტრო კარდიოგრამა',
            ]),
            'duration_minutes' => fake()->numberBetween(1, 12) * 5,
        ];
    }
}
