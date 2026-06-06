<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'duration_minutes',
    ];

    public function schedules()
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_service');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
