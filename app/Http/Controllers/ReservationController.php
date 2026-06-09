<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $doctor = Doctor::findOrFail($request->input('doctor_id'));

        $service = $doctor->services()
            ->whereKey($request->input('service_id'))
            ->firstOrFail();

        $start = CarbonImmutable::parse($request->input('start'));
        $end = $start->addMinutes($service->duration_minutes);

        $patient = Patient::updateOrCreate(
            [
                'id_number' => $request->input('patient.id_number'),
            ],
            [
                'firstname' => $request->input('patient.firstname'),
                'lastname' => $request->input('patient.lastname'),
                'phone' => $request->input('patient.phone'),
                'email' => $request->input('patient.email'),
            ]
        );

        $reservation = new Reservation();
        $reservation->patient_id = $patient->id;
        $reservation->doctor_id = $doctor->id;
        $reservation->service_id = $service->id;
        $reservation->start = $start;
        $reservation->end = $end;
        $reservation->save();

        return response()->json([
            'message' => 'ჯავშანი წარმატებით შეიქმნა',
            'data' => $reservation,
        ], 201);
    }
}
