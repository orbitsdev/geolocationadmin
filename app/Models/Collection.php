<?php

namespace App\Models;

use App\Models\Council;
use App\Models\CollectionItem;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Collection extends Model
{
    use HasFactory;

    public const LINE_CHART = 'Line Chart';
    public const BAR_CHART = 'Bar Chart';
    public const PIE_CHART = 'Pie Chart';
    public const SCATTER_CHART = 'Scatter Chart';
    public const RADAR_CHART = 'Radar Chart';

    public const CHART_OPTIONS = [
        self::LINE_CHART => self::LINE_CHART,
        self::BAR_CHART => self::BAR_CHART,
        self::PIE_CHART => self::PIE_CHART,
        self::SCATTER_CHART => self::SCATTER_CHART,
        self::RADAR_CHART => self::RADAR_CHART,
    ];

    public function council()
    {
        return $this->belongsTo(Council::class, 'council_id');
    }
    public function councilPosition()
    {
        return $this->belongsTo(CouncilPosition::class, 'council_position_id');
    }

    public function collectionItems(){
        return $this->hasMany(CollectionItem::class, 'collection_id');
    }

    public function scopeByCouncil($query, $councilId)
    {
        return $query->whereHas('councilPosition.council', function ($q) use ($councilId) {
            $q->where('id', $councilId);
        });
    }

    public function addItem($itemData)
{
    return $this->collectionItems()->create($itemData);
}
public function removeItem($itemId)
{
    return $this->collectionItems()->where('id', $itemId)->delete();
}
public function getWithRelations()
{
    return $this->with(['councilPosition', 'collectionItems'])->find($this->id);
}
    

}
