<?php

namespace App\Models;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CollectionItem extends Model
{
    use HasFactory;


    protected $casts = [
        'amount' => 'double',
    ];
    
    
    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
}
