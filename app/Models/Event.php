<?php

namespace App\Models;

use App\Models\Council;
use App\Models\Attendance;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius' => 'float',        
        'is_active' => 'boolean',
        'restrict_event' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    
    public function councilPosition()
    {
        return $this->belongsTo(CouncilPosition::class);
    }

    public function council(){
        return $this->belongsTo(Council::class);
    }

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }

}
