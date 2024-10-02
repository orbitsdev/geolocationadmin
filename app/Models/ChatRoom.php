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

    public function getName()
    {
        // Check if the chat room has a name, otherwise generate a default name
        $name = $this->name ?? 'Chat Room ' . $this->id;
    
        // If the council relation is loaded and exists, append the council's name
        if ($this->council) {
            $name .= ' - ' . $this->council->name;
        }

        return $name;
    }

}
