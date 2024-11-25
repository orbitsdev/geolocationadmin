<?php

namespace App\Models;

use App\Models\Post;
use App\Models\ChatRoom;
use App\Models\Collection;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Council extends Model
{
    use HasFactory;
    protected $casts = [
       
        'is_active' => 'boolean',
        
    ];

    public function councilPositions(){
        return $this->hasMany(CouncilPosition::class);
    }
    public function loadRelations()
    {
        return $this->load([
            'councilPositions',
            
        ]);
    }
    public function posts(){
        return $this->hasMany(Post::class);
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('created_at', $year);
    }

    public function scopeWithData($query)
    {
        return $query->with(['councilPositions', 'collections', 'chatRooms']);
    }

    public function getPositions()
    {
        return $this->councilPositions()->get();
    }

    public function getCollections()
    {
        return $this->collections()->get();
    }

    public function getChatRooms()
    {
        return $this->chatRooms()->get();
    }

    public function chatRoom(){
        return $this->hasOne(ChatRoom::class);
    }

}
