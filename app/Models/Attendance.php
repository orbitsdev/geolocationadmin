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

        'check_in_coordinates' => 'array',
        'check_out_coordinates' => 'array',
        'attendance_time' => 'datetime',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'attendance_allowed' => 'boolean',

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


    // Mark the attendance as checked in
    public function markCheckIn($latitude, $longitude)
    {
        $this->update([
            'check_in_time' => now(),
            'check_in_coordinates' => [
                'latitude' => (string)$latitude, // Save as string to avoid precision loss
                'longitude' => (string)$longitude,
            ],
            'status' => 'checked_in',
        ]);
    }

    /**
     * Mark Attendance as Checked Out
     */
    public function markCheckOut($latitude, $longitude)
    {
        $this->update([
            'check_out_time' => now(),
            'check_out_coordinates' => [
                'latitude' => (string)$latitude, // Save as string to avoid precision loss
                'longitude' => (string)$longitude,
            ],
            'status' => 'checked_out',
        ]);
    }
    public function registerMediaCollections(): void
    {


        $this->addMediaCollection('check_in_selfies')
            
            ->singleFile();


        $this->addMediaCollection('check_out_selfies')
            
            ->singleFile();
    }



    public function getCheckInLatitudeAttribute()
    {
        return (double)$this->check_in_coordinates['latitude'] ?? null;
    }

    public function getCheckInLongitudeAttribute()
    {
        return (double)$this->check_in_coordinates['longitude'] ?? null;
    }

    /**
     * Accessors for Check-Out Coordinates
     */
    public function getCheckOutLatitudeAttribute()
    {
        return (double)$this->check_out_coordinates['latitude'] ?? null;
    }

    public function getCheckOutLongitudeAttribute()
    {
        return (double)$this->check_out_coordinates['longitude'] ?? null;
    }


}
