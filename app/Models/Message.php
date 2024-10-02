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
    public function councilPosition(){
        return $this->belongsTo(CouncilPosition::class);
    }

    public function scopeByChatRoom($query, $chatRoomId)
{
    return $query->where('chat_room_id', $chatRoomId);
}

public function scopeByCouncilPosition($query, $councilPositionId)
{
    return $query->where('council_position_id', $councilPositionId);
}


public function scopeWithinDateRange($query, $startDate, $endDate)
{
    return $query->whereBetween('created_at', [$startDate, $endDate]);
}

public static function getLastMessageByChatRoom($chatRoomId)
{
    return self::where('chat_room_id', $chatRoomId)->latest()->first();
}

public function getPreview()
{
    return strlen($this->content) > 100 ? substr($this->content, 0, 100) . '...' : $this->content;
}

public function scopeByCouncil($query, $councilId)
{
    return $query->whereHas('councilPosition', function ($q) use ($councilId) {
        $q->where('council_id', $councilId);
    });
}

}
