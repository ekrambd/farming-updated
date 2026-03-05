<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productrating extends Model
{
    use HasFactory;

    public function farmeritem()
    {
    	return $this->belongsTo(Farmeritem::class)
    }
}
