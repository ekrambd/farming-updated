<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmercategory extends Model
{
    use HasFactory;

    // public function farmeritems()
    // {
    // 	return $this->hasMany(Farmeritem::class);
    // }

    public function farmersubcategories()
    {
    	return $this->hasMany(Farmersubcategory::class);
    }
    

    public function farmeritems()
	{
	    return $this->belongsToMany(Farmeritem::class);
	}
    
}
