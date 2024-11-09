<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected function casts(): array
    {
        return [
           
            'data' => 'array',
            'read_at' => 'datetime:M d,Y h:i:s A',
            
        ];
    }
}
