<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orderlog extends Model
{
    use HasFactory;

    public function order()
    {
    	return $this->belongsTo(Order::class);
    }

    public function farmeritems()
    {
        //return $this->belongsTo(FarmerItem::class, 'farmeritem_id');
        return $this->hasMany(Farmeritem::class, 'farmeritem_id');
    }
}
