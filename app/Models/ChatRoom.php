<?php

namespace App\Models;

use App\Models\Council;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatRoom extends Model
{
    use HasFactory;

    public function council(){
        return $this->belongsTo(Council::class);
    }

    public function messages(){
        return $this->hasMany(Message::class);
    }
}
