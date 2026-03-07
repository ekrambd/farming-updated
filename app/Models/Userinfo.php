<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userinfo extends Model
{
    use HasFactory;
    
    public function user()
    {
    	return $this->belongsTo(User::class);
    }
    
    public function farmercategory()
    {
        return $this->belongsTo(Farmercategory::class, 'farmercategory_id');
    }
    
    public function farmersubcategory()
    {
        return $this->belongsTo(Farmersubcategory::class, 'farmersubcategory_id');
    }

    public function locations()
    {
        $data = getLocation($this->id);
        return $data;
    }
    
}
