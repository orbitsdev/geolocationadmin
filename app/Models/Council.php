<?php

namespace App\Models;

use App\Models\ChatRoom;
use App\Models\Collection;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Council extends Model
{
    use HasFactory;

    public function councilPositions(){
        return $this->hasMany(CouncilPosition::class);
    }
    public function collections(){
        return $this->hasMany(Collection::class);
    }

    public static function suggestion(){
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;

        return "{$currentYear}-{$nextYear}";
    }

    public function chatRooms(){
        return $this->hasMany(ChatRoom::class);
    }

}
