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

    public function farmeritem()
	{
	    return $this->belongsTo(Farmeritem::class, 'item_id');
	}
}
