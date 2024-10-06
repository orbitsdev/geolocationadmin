<?php

namespace App\Models;

use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function councilPosition()
    {
        return $this->belongsTo(CouncilPosition::class, 'council_position_id');
    }

    public function scopeByCouncil($query, $councilId)
    {
        return $query->whereHas('councilPosition.council', function ($q) use ($councilId) {
            $q->where('id', $councilId);
        });
    }

    public function scopeWithPostRelations($query)
    {
        return $query->with([
            'councilPosition',
            'file',
            'files'
        ]);
    }
    public function loadPostRelations()
    {
        return $this->load([
            'councilPosition',
            'file',
            'files'
        ]);
    }

}
