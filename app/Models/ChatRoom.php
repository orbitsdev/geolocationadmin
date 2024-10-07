<?php

namespace App\Models;

use App\Models\Council;
use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatRoom extends Model
{
    use HasFactory;

    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getName()
    {
        $name = $this->name ?? 'Chat Room ' . $this->id;

        if ($this->council) {
            $name .= ' - ' . $this->council->name;
        }

        return $name;
    }

    public function lastMessage()
    {
        return $this->messages()->latest()->first();
    }

    public function scopeByCouncil($query, $councilId)
    {
        return $query->where('council_id', $councilId);
    }


    public function getMessages()
    {
        return $this->messages()->get();
    }

    public function scopeWithMessages($query){
        return $query->with('messages');
    }

}
