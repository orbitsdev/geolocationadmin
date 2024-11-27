<?php

namespace App\Models;

use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class Post extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $casts = [
        'is_publish' => 'boolean',
    ];


    public function registerMediaCollections(): void
    {

        $this->addMediaCollection('post_media');

    }
    public function postable()
    {
        return $this->morphTo();
    }

    // public function files(): MorphMany
    // {
    //     return $this->morphMany(File::class, 'fileable');
    // }
    // public function file(): MorphOne
    // {
    //     return $this->morphOne(File::class, 'fileable');
    // }

    public function council()
    {
        return $this->belongsTo(Council::class, 'council_id');
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
            'council',
            'media'
            // 'file',
            // 'files'
        ]);
    }
    public function loadPostRelations()
    {
        return $this->load([
            'council',
            'councilPosition',
            'media',
            // 'files'
        ]);
    }

}
