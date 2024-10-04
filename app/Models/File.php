<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getImage()
    {
        if (!empty($this->file) && Storage::disk('public')->exists($this->file)) {
            return Storage::disk('public')->url($this->file);
        } else {
            return asset('images/placeholder-image.jpg'); // Return default image URL
        }
    }

    public function getFileUrlAttribute()
    {
        return Storage::url($this->file);
    }

}
