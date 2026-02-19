<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmerunit extends Model
{
    use HasFactory;
    
    public function farmeritems()
    {
        return $this->hasMany(Farmeritem::class);
    }
}
