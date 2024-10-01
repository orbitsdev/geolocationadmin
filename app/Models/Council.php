<?php

namespace App\Models;

use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Council extends Model
{
    use HasFactory;

    public function councilPositions(){
        return $this->hasMany(CouncilPosition::class);
    }

}
