<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Reservation;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+7 days');
        $duration = fake()->numberBetween(1, 12) * 5;
        $end = (clone $start)->modify("+{$duration} minutes");

        return [
            'patient_id' => Patient::query()->inRandomOrder()->value('id'),
            'doctor_id' => Doctor::query()->inRandomOrder()->value('id'),
            'service_id' => Service::query()->inRandomOrder()->value('id'),
            'reservation_start' => $start,
            'reservation_end' => $end,
        ];
    }
}
