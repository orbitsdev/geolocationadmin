<?php

namespace App\Models;

use App\Models\File;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class Task extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'council_position_id',
        'approved_by_council_position_id',
        'title',
        'task_details',
        'due_date',
        'completed_at',
        'status',
        'status_changed_at',  // Include status_changed_at in fillable
        'remarks',  // Include remarks in fillable
        'is_lock',
        'is_done',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'status_changed_at' => 'datetime',  // Cast status_changed_at as datetime
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_lock' => 'boolean',
        'is_done' => 'boolean',
        'status' => 'string',
    ];



    public const STATUS_TODO = 'To Do';
    public const STATUS_IN_PROGRESS = 'In Progress';
    public const STATUS_COMPLETED = 'Done';
    public const STATUS_COMPLETED_LATE = 'Completed Late';
    public const STATUS_DUE = 'Due / Pending';
    public const STATUS_ON_HOLD = 'On Hold';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_REVIEW = 'Review';
    public const STATUS_BLOCKED = 'Blocked';

    public const STATUS_OPTIONS = [
        self::STATUS_TODO => self::STATUS_TODO,
        self::STATUS_IN_PROGRESS => self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED => self::STATUS_COMPLETED,
        self::STATUS_COMPLETED_LATE => self::STATUS_COMPLETED_LATE,
        self::STATUS_DUE => self::STATUS_DUE,
        self::STATUS_ON_HOLD => self::STATUS_ON_HOLD,
        self::STATUS_CANCELLED => self::STATUS_CANCELLED,
        self::STATUS_REVIEW => self::STATUS_REVIEW, // Corrected here
        self::STATUS_BLOCKED => self::STATUS_BLOCKED,
    ];




    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function assignedCouncilPosition()
    {
        return $this->belongsTo(CouncilPosition::class, 'council_position_id');
    }

    public function approvedByCouncilPosition()
    {
        return $this->belongsTo(CouncilPosition::class, 'approved_by_council_position_id');
    }

    public function councilPosition()
    {
        return $this->belongsTo(CouncilPosition::class, 'council_position_id');
    }


    public function addFile($filePath)
    {
        return $this->files()->create(['path' => $filePath]);
    }

    public function removeFile($fileId)
    {
        return $this->files()->where('id', $fileId)->delete();
    }



    public function approvedBy()
    {
        return $this->belongsTo(CouncilPosition::class, 'approved_by_council_position_id');
    }

    public function approve(CouncilPosition $approver)
    {
        $this->approved_by_council_position_id = $approver->id;
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        $this->save();
    }


public function scopeByStatus($query, $status)
{
    return $query->where('status', $status);
}


public function scopeByCouncilPosition($query, $councilPositionId)
{
    return $query->where('council_position_id', $councilPositionId);
}
public function scopeByCouncil($query, $councilId)
{
    return $query->whereHas('assignedCouncilPosition', function ($q) use ($councilId) {
        $q->where('council_id', $councilId);
    });
}

public function scopeApproved($query)
{
    return $query->whereNotNull('approved_by_council_position_id');
}

public function checkForLateCompletion()
{
    if ($this->status === self::STATUS_COMPLETED && $this->completed_at && $this->due_date && $this->completed_at > $this->due_date) {
        $this->status = self::STATUS_COMPLETED_LATE;
        $this->save();
    }
}
public function scopeWithTaskRelations($query)
{                        
    return $query->latest()->with([
        'assignedCouncilPosition',
        'approvedByCouncilPosition',
        'media'
        
    ]);
}
public function scopeTaskBelongToCouncilOf($query ,$councilId)
{
    return $query->whereHas('councilPosition', function($q) use($councilId){
        return $q->where('council_id', $councilId);
    });
}
public function loadTaskRelations()
    {
        return $this->load([
            'assignedCouncilPosition',
            'approvedByCouncilPosition',
            'file',
            'files'
        ]);
    }

    public function registerMediaCollections(): void
    {

        $this->addMediaCollection('task_media');

    }

    
public function getImage()
{

   
    if ($this->hasMedia('task_media')) {
        return $this->getFirstMediaUrl('task_media');
    }

    return asset('images/placeholder-image.jpg');
}

}
