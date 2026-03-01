<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function orderdetails()
    {
    	return $this->hasMany(Orderdetail::class);
    }

    public function orderlogs()
    {
    	return $this->hasMany(Orderlog::class);
    }
}
