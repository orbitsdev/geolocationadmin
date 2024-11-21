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

        'check_in_coordinates' => 'json',
        'check_out_coordinates' => 'json',
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
    public function registerMediaCollections(): void
    {


        $this->addMediaCollection('check_in_selfies')
            
            ->singleFile();


        $this->addMediaCollection('check_out_selfies')
            
            ->singleFile();
    }



    public function getCheckInLatitudeAttribute()
    {
        return isset($this->check_in_coordinates['latitude'])
            ? (double) $this->check_in_coordinates['latitude']
            : null;
    }

    // Accessor for check-in longitude
    public function getCheckInLongitudeAttribute()
    {
        return isset($this->check_in_coordinates['longitude'])
            ? (double) $this->check_in_coordinates['longitude']
            : null;
    }

    // Accessor for check-out latitude
    public function getCheckOutLatitudeAttribute()
    {
        return isset($this->check_out_coordinates['latitude'])
            ? (double) $this->check_out_coordinates['latitude']
            : null;
    }

    // Accessor for check-out longitude
    public function getCheckOutLongitudeAttribute()
    {
        return isset($this->check_out_coordinates['longitude'])
            ? (double) $this->check_out_coordinates['longitude']
            : null;
    }


}
