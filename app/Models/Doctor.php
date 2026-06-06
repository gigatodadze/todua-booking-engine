<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'firstname',
        'lastname',
    ];

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'doctor_service');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
