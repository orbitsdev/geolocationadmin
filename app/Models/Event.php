<?php

namespace App\Models;

use App\Models\Council;
use App\Models\CouncilPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    public function councilPosition()
    {
        return $this->belongsTo(CouncilPosition::class);
    }

    public function council(){
        return $this->belongsTo(Council::class);
    }

}
