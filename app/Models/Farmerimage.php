<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmerimage extends Model
{
    use HasFactory;

    protected $fillable = ['farmeritem_id', 'image_path'];

    public function farmeritem()
    {
        return $this->belongsTo(FarmerItem::class, 'farmeritem_id');
    }
}
