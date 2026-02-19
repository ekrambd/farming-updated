<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farmercategory;
use App\Models\Farmersubcategory;
use App\Models\Farmerunit;

class AjaxController extends Controller
{
    public function categoryStatusUpdate(Request $request)
    {
    	try
    	{
    		$category = Farmercategory::findorfail($request->category_id);
    		$category->status = $request->status;
    		$category->update();
    		return response()->json(['status'=>true, 'message'=>"Successfully the category's status updated"]);
    	}catch(Exception $e){
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }

    public function subcategoryStatusUpdate(Request $request)
    {
    	try
    	{
    		$subcategory = Farmersubcategory::findorfail($request->subcategory_id);
    		$subcategory->status = $request->status;
    		$subcategory->update();
    		return response()->json(['status'=>true, 'message'=>"Successfully the subcategory's status updated"]);
    	}catch(Exception $e){
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }

    public function unitStatusUpdate(Request $request)
    {
        try
        {
            $unit = Farmerunit::findorfail($request->unit_id);
            $unit->status = $request->status;
            $unit->update();
            return response()->json(['status'=>true, 'message'=>"Successfully the unit's status has been updated"]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
}
