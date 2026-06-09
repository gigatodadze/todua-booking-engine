<?php

namespace App\Http\Requests;

use App\Models\Doctor;
use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('patient.phone')) {
            $normalizedPhone = preg_replace('/\D/', '', $this->input('patient.phone'));
            $normalizedPhone = preg_replace('/^995/', '', $normalizedPhone);

            $patient = $this->input('patient', []);
            $patient['phone'] = $normalizedPhone;

            $this->merge([
                'patient' => $patient,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'service_id' => [
                'required',
                'integer',
                Rule::exists('doctor_service', 'service_id')
                    ->where('doctor_id', $this->input('doctor_id')),
            ],
            'start' => ['required', 'string', 'date', 'after:now'],
            'patient.firstname' => ['required', 'string', 'max:255'],
            'patient.lastname' => ['required', 'string', 'max:255'],
            'patient.phone' => ['required', 'string', 'digits:9', 'starts_with:5'],
            'patient.email' => ['nullable', 'string', 'email:filter,dns', 'max:255'],
            'patient.id_number' => ['required', 'string', 'digits:11'],
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'ექიმის არჩევა სავალდებულოა',
            'doctor_id.exists' => 'არჩეული ექიმი არ არსებობს',

            'service_id.required' => 'სერვისის არჩევა სავალდებულოა',
            'service_id.exists' => 'ექიმს ამ სერვისის შესრულება არ შეუძლია',

            'start.required' => 'დაჯავშნის დაწყების დრო სავალდებულოა',
            'start.date' => 'გთხოვთ შეიყვანოთ დაჯავშნის დრო სწორი ფორმატით',
            'start.after' => 'ჯავშნის დრო უნდა იყოს მომავალში',

            'patient.firstname.required' => 'პაციენტის სახელი სავალდებულოა',
            'patient.lastname.required' => 'პაციენტის გვარი სავალდებულოა',

            'patient.phone.required' => 'პაციენტის ტელეფონის ნომერი სავალდებულოა',
            'patient.phone.digits' => 'ტელეფონის ნომერი უნდა შედგებოდეს 9 ციფრისგან',
            'patient.phone.starts_with' => 'ტელეფონის ნომერი უნდა იწყებოდეს 5-ით',

            'patient.email.email' => 'ელ-ფოსტის ფორმატი არასწორია',

            'patient.id_number.required' => 'პირადი ნომერი სავალდებულოა',
            'patient.id_number.digits' => 'პირადი ნომერი უნდა შედგებოდეს 11 ციფრისგან',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $doctor = Doctor::find($this->input('doctor_id'));

                if (!$doctor) {
                    return;
                }

                $service = $doctor->services()
                    ->whereKey($this->input('service_id'))
                    ->first();

                if (!$service) {
                    return;
                }

                $start = CarbonImmutable::parse($this->input('start'));
                $end = $start->addMinutes($service->duration_minutes);
                $weekday = $start->dayOfWeekIso;

                $doctorWorksAtThatTime = $doctor->schedules()
                    ->where('weekday', $weekday)
                    ->whereTime('start_time', '<=', $start)
                    ->whereTime('end_time', '>=', $end)
                    ->exists();

                if (!$doctorWorksAtThatTime) {
                    $validator->errors()->add('start', 'ექიმი არ მუშაობს ამ საათებში');

                    return;
                }

                $serviceAvailableAtThatTime = $service->schedules()
                    ->where('weekday', $weekday)
                    ->whereTime('start_time', '<=', $start)
                    ->whereTime('end_time', '>=', $end)
                    ->exists();

                if (!$serviceAvailableAtThatTime) {
                    $validator->errors()->add('start', 'სერვისი მიუწვდომელია ამ დროს');

                    return;
                }

                $hasConflict = Reservation::query()
                    ->where('doctor_id', $doctor->id)
                    ->where('start', '<', $end)
                    ->where('end', '>', $start)
                    ->exists();

                if ($hasConflict) {
                    $validator->errors()->add('start', 'აღნიშნული დრო უკვე დაკავებულია');
                }
            },
        ];
    }
}
