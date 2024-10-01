<?php

namespace App\Models;

use App\Models\File;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;


    public const STATUS_TODO = 'To Do';
    public const STATUS_IN_PROGRESS = 'In Progress';
    public const STATUS_COMPLETED = 'Completed / Done';
    public const STATUS_COMPLETED_LATE = 'Completed Late';
    public const STATUS_DUE = 'Due / Pending';
    public const STATUS_ON_HOLD = 'On Hold';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_REVIEW = 'Review';
    public const STATUS_BLOCKED = 'Blocked';

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function councilPosition()
    {
        return $this->belongsTo(CouncilPosition::class, 'council_position_id');
    }


    public function approvedBy()
    {
        return $this->belongsTo(CouncilPosition::class, 'approved_by_council_position_id');
    }
}
