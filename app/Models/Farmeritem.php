<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmeritem extends Model
{
    use HasFactory;

    protected $appends = ['discount_price'];

    protected $fillable = [
        'user_id',
        'farmerunit_id',
        // 'farmercategory_id',
        // 'farmersubcategory_id',
        'item_name',
        'item_name_bn',
        'price',
        'discount',
        'stock_qty',
        'description',
        'featured_image',
        'hit_count',
        'status',
    ];

    public function images()
    {
        return $this->hasMany(Farmerimage::class, 'farmeritem_id');
    }

    public function orderlog()
    {
        return $this->belongsTo(Orderlog::class, 'item_id');
    }

    // public function farmercategory()
    // {
    // 	return $this->belongsTo(Farmercategory::class);
    // }

    // public function farmersubcategory()
    // {
    // 	return $this->belongsTo(Farmersubcategory::class);
    // }

    public function getDiscountPriceAttribute()
    {
        $price = itemPrice($this->id);
        return strval($price);
    }
    
    public function farmerunit()
    {
        return $this->belongsTo(Farmerunit::class);
    }

    public function farmercategories()
    {
        return $this->belongsToMany(Farmercategory::class);
    }

    public function farmersubcategories()
    {
        return $this->belongsToMany(Farmersubcategory::class)->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productratings()
    {
        return $this->hasMany(Productrating::class);
    }

}
