<?php

namespace App\Models;

use App\Models\ChatRoom;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    public function chatRoom(){
        return $this->belongsTo(ChatRoom::class);
    }
    public function counsilPosition(){
        return $this->belongsTo(CouncilPosition::class);
    }
}
