<?php

namespace Database\Seeders;

use App\Models\DoctorSchedule;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        DoctorSchedule::create([
            'doctor_id' => 1,
            'weekday' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        DoctorSchedule::create([
            'doctor_id' => 2,
            'weekday' => 1,
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
        ]);

        DoctorSchedule::create([
            'doctor_id' => 3,
            'weekday' => 1,
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);
    }
}
