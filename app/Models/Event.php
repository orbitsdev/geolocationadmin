<?php
namespace App\Models;

use App\Models\Council;
use App\Models\Attendance;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::deleting(function ($event) {
            // Delete associated post if it exists
            if ($event->post) {
                $event->post->delete();
            }
        });
    }

    protected $casts = [

        'latitude' => 'double',
        'longitude' => 'double',
        'radius' => 'double',
        'is_active' => 'boolean',
        'restrict_event' => 'boolean',
        'is_publish' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function post(): MorphOne
    {
        return $this->morphOne(Post::class, 'postable');
    }

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

    public function scopeWithRelation($query){
        return $query->whereHas('council')->latest()->with(['councilPosition','council'])
        ->withCount('attendances');
    }

    public function scopeWithRelationAndActiveAttendance($query)
{
    return $query->latest()->with(['councilPosition', 'council'])
                 ->withCount(['attendances' => function ($query) {
                     $query->where('status', 'active');
                 }]);
}


public function hasCheckedOut($councilPositionId)
    {
        return $this->attendances()
            ->where('council_position_id', $councilPositionId)
            ->whereNotNull('check_out_time')
            ->exists();
    }

    public function getAttendanceForCouncilPosition($councilPositionId)
    {
        return $this->attendances()
            ->where('council_position_id', $councilPositionId)
            ->first();
    }

}
