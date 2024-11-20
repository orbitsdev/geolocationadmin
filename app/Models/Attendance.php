<?php

namespace App\Models;

use App\Models\Event;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class Attendance extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;


    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function councilPosition()
    {
        return $this->belongsTo(CouncilPosition::class, 'council_position_id');
    }
    public function event()
    {
        return $this->belongsTo(Event::class);
    }


    public function scopeWithRelations($query)
    {
        return $query->with([
            'councilPosition',
            'event',
        ]);
    }
    public function loadRelations()
    {
        return $this->load([
            'councilPosition',
            'event',

        ]);
    }

    public function hasCheckedIn()
    {
        return !is_null($this->check_in_time);
    }

    // Check if a user has already checked out
    public function hasCheckedOut()
    {
        return !is_null($this->check_out_time);
    }

    // Mark the attendance as checked in
    public function markCheckIn($latitude, $longitude)
    {
        $this->update([
            'check_in_time' => now(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'status' => 'checked_in',
        ]);
    }

    // Mark the attendance as checked out
    public function markCheckOut($latitude, $longitude)
    {
        $this->update([
            'check_out_time' => now(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'status' => 'checked_out',
        ]);
    }
}

