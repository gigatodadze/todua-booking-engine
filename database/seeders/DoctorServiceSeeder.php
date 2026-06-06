<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DoctorServiceSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = Doctor::all();
        $services = Service::all();

        foreach ($doctors as $doctor) {
            $doctor->services()->attach(
                $services->random(rand(1, min(3, $services->count())))->pluck('id')->toArray()
            );
        }
    }
}
