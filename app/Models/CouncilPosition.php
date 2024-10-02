<?php

namespace App\Models;

use App\Models\Post;
use App\Models\Task;
use App\Models\User;
use App\Models\Council;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CouncilPosition extends Model
{
    use HasFactory;

    public function council(){
        return $this->belongsTo(Council::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function tasks(){
        return $this->hasMany(Task::class);
    }
    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function scopeCouncelBatch($query, $councilId){
       return $query->where('council_id', $councilId);
    }

}
