<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PatientSeeder::class,
            DoctorSeeder::class,
            ServiceSeeder::class,
            DoctorServiceSeeder::class,
            DoctorScheduleSeeder::class,
            ServiceScheduleSeeder::class,
            ReservationSeeder::class,
        ]);
    }
}
