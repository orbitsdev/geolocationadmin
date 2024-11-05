<?php

namespace App\Models;

use App\Models\Post;
use App\Models\Task;
use App\Models\User;
use App\Models\Council;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CouncilPosition extends Model
{
    use HasFactory;
    protected $casts = [
        'is_login' => 'boolean',
        'grant_access' => 'boolean',

    ];

    public function council(){
        return $this->belongsTo(Council::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function fullName(): string
    {

        return $this->user ? $this->user->fullName() : 'No user assigned';
    }
    public function tasks(){
        return $this->hasMany(Task::class);
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }
    public function messages(){
        return $this->hasMany(Message::class);
    }

    public function getMessages()
    {
        return $this->messages()->get();
    }

    public function getMessagesByChatRoom($chatRoomId)
    {
        return $this->messages()->where('chat_room_id', $chatRoomId)->get();
    }


    public function tasksByStatus($status){
        return $this->tasks()->where('status', $status)->get();
    }

    public static function positionsByCouncil($councilId)
    {
        return self::where('council_id', $councilId)->get();
    }

    public function scopeNotLogin($query){
        $query->where('is_login', false);
    }

    public function scopeWithRelation($query)
    {
        $query->with(['user','council']);
    }

    public function scopeWithTaskCounts($query)
    {
        return $query->withCount([
            'tasks as total_to_do_tasks' => function ($query) {
                $query->where('status', Task::STATUS_TODO);
            },
            'tasks as total_in_progress_tasks' => function ($query) {
                $query->where('status', Task::STATUS_IN_PROGRESS);
            },
            'tasks as total_completed_tasks' => function ($query) {
                $query->where('status', Task::STATUS_COMPLETED);
            },
            'tasks as total_completed_late_tasks' => function ($query) {
                $query->where('status', Task::STATUS_COMPLETED_LATE);
            },
            'tasks as total_due_tasks' => function ($query) {
                $query->where('status', Task::STATUS_DUE);
            },
            'tasks as total_on_hold_tasks' => function ($query) {
                $query->where('status', Task::STATUS_ON_HOLD);
            },
            'tasks as total_cancelled_tasks' => function ($query) {
                $query->where('status', Task::STATUS_CANCELLED);
            },
            'tasks as total_review_tasks' => function ($query) {
                $query->where('status', Task::STATUS_REVIEW);
            },
            'tasks as total_blocked_tasks' => function ($query) {
                $query->where('status', Task::STATUS_BLOCKED);
            },
        ]);
    
    }
    



}
