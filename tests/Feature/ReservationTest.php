<?php

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\ServiceSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createReservationSlot(): array
{
    $doctor = Doctor::factory()->create();

    $service = Service::factory()->create([
        'name' => 'Test Service ' . fake()->uuid(),
        'duration_minutes' => 30,
    ]);

    $doctor->services()->attach($service->id);

    $start = CarbonImmutable::now()->addWeek()->setTime(15, 0);
    $end = $start->addMinutes($service->duration_minutes);
    $weekday = $start->dayOfWeekIso;

    DoctorSchedule::create([
        'doctor_id' => $doctor->id,
        'weekday' => $weekday,
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
    ]);

    ServiceSchedule::create([
        'service_id' => $service->id,
        'weekday' => $weekday,
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
    ]);

    return compact('doctor', 'service', 'start', 'end');
}

function validReservationPayload(Doctor $doctor, Service $service, CarbonImmutable $start): array
{
    return [
        'doctor_id' => $doctor->id,
        'service_id' => $service->id,
        'start' => $start->toDateTimeString(),
        'patient' => [
            'firstname' => 'გიგა',
            'lastname' => 'თოდაძე',
            'phone' => '+995 558 31 39 33',
            'email' => 'g.todadze@developers-alliance.com',
            'id_number' => '12345678911',
        ],
    ];
}

it('creates a reservation successfully', function () {
    ['doctor' => $doctor, 'service' => $service, 'start' => $start, 'end' => $end] = createReservationSlot();

    $response = $this->postJson('/api/reservations', validReservationPayload($doctor, $service, $start));

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'ჯავშანი წარმატებით შეიქმნა');

    $this->assertDatabaseHas('patients', [
        'id_number' => '12345678911',
        'phone' => '558313933',
    ]);

    $this->assertDatabaseHas('reservations', [
        'doctor_id' => $doctor->id,
        'service_id' => $service->id,
        'start' => $start->toDateTimeString(),
        'end' => $end->toDateTimeString(),
    ]);
});

it('rejects service that does not belong to selected doctor', function () {
    $doctor = Doctor::factory()->create();

    $service = Service::factory()->create([
        'name' => 'Test Service ' . fake()->uuid(),
        'duration_minutes' => 30,
    ]);

    $start = CarbonImmutable::now()->addWeek()->setTime(15, 0);

    $response = $this->postJson('/api/reservations', validReservationPayload($doctor, $service, $start));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['service_id'])
        ->assertJsonPath('errors.service_id.0', 'ექიმს ამ სერვისის შესრულება არ შეუძლია');
});

it('rejects reservation when doctor does not work at selected time', function () {
    $doctor = Doctor::factory()->create();

    $service = Service::factory()->create([
        'name' => 'Test Service ' . fake()->uuid(),
        'duration_minutes' => 30,
    ]);

    $doctor->services()->attach($service->id);

    $start = CarbonImmutable::now()->addWeek()->setTime(21, 0);
    $weekday = $start->dayOfWeekIso;

    DoctorSchedule::create([
        'doctor_id' => $doctor->id,
        'weekday' => $weekday,
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
    ]);

    ServiceSchedule::create([
        'service_id' => $service->id,
        'weekday' => $weekday,
        'start_time' => '09:00:00',
        'end_time' => '23:00:00',
    ]);

    $response = $this->postJson('/api/reservations', validReservationPayload($doctor, $service, $start));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start'])
        ->assertJsonPath('errors.start.0', 'ექიმი არ მუშაობს ამ საათებში');
});

it('rejects reservation when service is not available at selected time', function () {
    $doctor = Doctor::factory()->create();

    $service = Service::factory()->create([
        'name' => 'Test Service ' . fake()->uuid(),
        'duration_minutes' => 30,
    ]);

    $doctor->services()->attach($service->id);

    $start = CarbonImmutable::now()->addWeek()->setTime(21, 0);
    $weekday = $start->dayOfWeekIso;

    DoctorSchedule::create([
        'doctor_id' => $doctor->id,
        'weekday' => $weekday,
        'start_time' => '09:00:00',
        'end_time' => '23:00:00',
    ]);

    ServiceSchedule::create([
        'service_id' => $service->id,
        'weekday' => $weekday,
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
    ]);

    $response = $this->postJson('/api/reservations', validReservationPayload($doctor, $service, $start));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start'])
        ->assertJsonPath('errors.start.0', 'სერვისი მითითებულ დროს მიუწვდომელია');
});

it('rejects overlapping reservation', function () {
    ['doctor' => $doctor, 'service' => $service, 'start' => $start, 'end' => $end] = createReservationSlot();

    $patient = Patient::factory()->create([
        'id_number' => '01001001001',
    ]);

    $reservation = new Reservation();
    $reservation->patient_id = $patient->id;
    $reservation->doctor_id = $doctor->id;
    $reservation->service_id = $service->id;
    $reservation->start = $start;
    $reservation->end = $end;
    $reservation->save();

    $response = $this->postJson('/api/reservations', validReservationPayload($doctor, $service, $start));

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start'])
        ->assertJsonPath('errors.start.0', 'აღნიშნული დრო უკვე დაკავებულია');
});

it('rejects invalid phone number', function () {
    ['doctor' => $doctor, 'service' => $service, 'start' => $start] = createReservationSlot();

    $payload = validReservationPayload($doctor, $service, $start);
    $payload['patient']['phone'] = '158 31 39 33';

    $response = $this->postJson('/api/reservations', $payload);
    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['patient.phone']);

    expect($response->json('errors')['patient.phone'][0])
        ->toBe('ტელეფონის ნომერი უნდა იწყებოდეს 5-ით');
});

it('rejects reservation in the past', function () {
    ['doctor' => $doctor, 'service' => $service] = createReservationSlot();

    $payload = validReservationPayload(
        $doctor,
        $service,
        CarbonImmutable::now()->subDay()
    );

    $response = $this->postJson('/api/reservations', $payload);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start'])
        ->assertJsonPath('errors.start.0', 'ჯავშნის დრო უნდა იყოს მომავალში');
});
