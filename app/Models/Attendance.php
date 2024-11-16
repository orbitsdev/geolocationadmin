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
}

