<?php

namespace App\Models;

use App\Models\CollectionItem;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Collection extends Model
{
    use HasFactory;

    public function councilPosition()
    {
        return $this->belongsTo(CouncilPosition::class, 'council_position_id');
    }

    public function collectionItems(){
        return $this->hasMany(CollectionItem::class, 'collection_id');
    }
}
