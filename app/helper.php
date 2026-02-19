<?php
 use App\Models\Farmercategory;
 use App\Models\Farmersubcategory;
 use App\Models\Farmeritem;

 function user()
 {
 	$user = auth()->user();
 	return $user;
 }

 function categories()
 {
 	$categories = Farmercategory::latest()->get();
 	return $categories;
 }

 function subcategories()
 {
 	$subcategories = Farmersubcategory::latest()->get();
 	return $subcategories;
 }

 function itemPrice($id)
{
	$item = Farmeritem::find($id);

	$original_price = $item->price; 

	if($item->discount == NULL){
		$discount_rate = 0;
	}else{
		$discount_rate = $item->discount/100; // % discount expressed as a decimal
	}

    $discount_amount = $original_price * $discount_rate;

    $finalAmount = $item->price - $discount_amount; 

    return $finalAmount;
}