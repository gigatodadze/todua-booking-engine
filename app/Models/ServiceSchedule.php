<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSchedule extends Model
{
    protected $table = 'service_schedule';

    protected $fillable = [
        'service_id',
        'weekday',
        'start_time',
        'end_time',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
