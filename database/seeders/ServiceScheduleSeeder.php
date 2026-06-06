<?php

namespace Database\Seeders;

use App\Models\ServiceSchedule;
use Illuminate\Database\Seeder;

class ServiceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        ServiceSchedule::create([
            'service_id' => 1,
            'weekday' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        ServiceSchedule::create([
            'service_id' => 2,
            'weekday' => 1,
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
        ]);

        ServiceSchedule::create([
            'service_id' => 3,
            'weekday' => 1,
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
        ]);

        ServiceSchedule::create([
            'service_id' => 4,
            'weekday' => 2,
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
        ]);

        ServiceSchedule::create([
            'service_id' => 5,
            'weekday' => 3,
            'start_time' => '11:00:00',
            'end_time' => '17:00:00',
        ]);
    }
}
