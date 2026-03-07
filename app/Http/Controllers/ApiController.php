<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farmercategory;
use App\Models\Farmersubcategory;
use App\Models\User;
use App\Models\Farmerslider;
use App\Models\Userinfo;
use App\Models\Farmerunit;
use App\Models\Farmeritem;
use App\Models\Farmerimage;
//use Aws\S3\S3Client
use Validator;
use Auth;
use DB;
use Hash;
use App\Models\Order;
use App\Models\Orderdetail;
use App\Models\Notification;
use App\Models\Orderlog;
use App\Models\Productrating;
use App\Models\Farmerrating;

class ApiController extends Controller
{

	public function categories(Request $request)
	{
	    try {

	        $query = Farmercategory::query();

	        if ($request->has('search') && !empty($request->search)) {
	            $search = $request->search;
	            $query->where('category_name', 'LIKE', "%{$search}%")->orWhere('category_name_bn', 'LIKE', "%{$search}%");
	        }

	        $query->where('status', 'Active');


	        $query->with(['farmersubcategories' => function ($q) {
	            $q->where('status', 'Active');
	        }]);

	        if ($request->is_paginate == 1) {

	            $per_page = $request->per_page ?? 10;

	            $data = $query->latest()->paginate($per_page);

	        } else {

	            $data = $query->latest()->get();
	        }

	        return response()->json([
	            'status' => true,
	            'data'   => $data
	        ]);

	    } catch (Exception $e) {

	        return response()->json([
	            'status'  => false,
	            'code'    => $e->getCode(),
	            'message' => $e->getMessage()
	        ], 500);
	    }
	}


	public function userSignup(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:50',
                'phone' => 'required|string',
                'email' => 'nullable|email',
                'address' => 'nullable|string',
                'password' => 'required|string',
                'confirm_password' => 'required|string|same:password',
                'profile_image' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $user = new User();

            // if(!$user || $user->role != 'user')
            // {
            //     return response()->json(['status'=>false, 'message'=>"Invalid User", "data"=>new \stdClass()],400);
            // }    

            if($request->has('email')){
                $countEmail = User::where('email',$request->email)->count();
                if($countEmail > 0){
                    return response()->json(['status'=>false, 'message'=>"Already the email has been taken", "data"=>new \stdClass()],400);
                }
            }

            if($request->has('phone')){
                $countPhone = User::where('phone',$request->phone)->count();
                if($countPhone > 0){
                    return response()->json(['status'=>false, 'message'=>"Already the phone has been taken", "data"=>new \stdClass()],400);
                }
            }

            if ($request->file('profile_image')) {
                $file = $request->file('profile_image');
                $name = time() . "profile_". $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/farmers/', $name);
                $path = 'uploads/farmers/' . $name;
            }else{
                $path = NULL;
            }

            $user = new User();
            $user->role = 'user';
            $user->full_name = $request->full_name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->address = $request->address;
            $user->image_path = $path;
            $user->save();

            return response()->json(['status'=>true, 'message'=>"Successfully Signup", "data"=>$user],201);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function userSignin(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'login' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $login = $request->input('login');
            $password = $request->input('password');

            $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            $user = User::where('email',$login)->orWhere('phone',$login)->first();
            
            if(!$user)
            {
                return response()->json(['status'=>false, 'message'=>'Invalid User ID', 'token'=>"", 'user'=>new \stdClass()],404);
            }
            
            if($user->status == 'Inactive'){
                return response()->json(['status'=>false, 'message'=>'Sorry you are not active user', 'token'=>"", 'user'=>new \stdClass()],403);
            }

            if (Auth::attempt([$fieldType => $login, 'password' => $password])) {
                $token = $user->createToken('MyApp')->plainTextToken;
                return response()->json(['status'=>true,'message'=>'Successfully Logged IN', 'token'=>$token, 'user'=>$user]);
            }

            return response()->json(['status'=>false,'message'=>"Invalid Email/Phone or Password", 'token'=>"", 'user'=>new \stdClass()],401);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function userSignOut(Request $request)
    {
        try
        {
            auth()->user()->tokens()->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully Logged Out']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function sliders()
    {
        try
        {
            $sliders = Farmerslider::latest()->get();
            return response()->json(['status'=>count($sliders) > 0, 'data'=>$sliders]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function farmerSignup(Request $request)
    {   
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string',
                'farmercategory_id' => 'nullable|integer|exists:farmercategories,id',
                'farmersubcategory_id' => 'nullable|integer|exists:farmersubcategories,id',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'password' => 'required|string',
                'nid_passport' => 'required|numeric',
                'confirm_password' => 'required|string|same:password',
                'categories' => 'required'
                // 'categories' => 'required|array',
                // 'categories.*' => 'exists:farmercategories,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            // var_dump($request->categories);
            // exit();

            $emailCheck = User::where('email', $request->email)->first();
            $phoneCheck = User::where('phone', $request->phone)->first();

            $nidCheck = Userinfo::where('nid_passport',$request->nid_passport)->first();

            if($emailCheck && $phoneCheck)
            {
                return response()->json(['status'=>false, 'message'=>'Email && Phone Both are already exist', 'data'=>new \stdClass()],400);
            }elseif($emailCheck){
                return response()->json(['status'=>false, 'message'=>'The Email already exist', 'data'=> new \stdClass()],400);
            }elseif($phoneCheck){
                return response()->json(['status'=>false, 'message'=>'The Phone already exist', 'data'=> new \stdClass()],400);
            }

            if($nidCheck)
            {
                return response()->json(['status'=>false, 'message'=>'The NID/Passport already exist', 'data'=> new \stdClass()],400);
            }

            if ($request->file('nid_front_photo')) {
                $file = $request->file('nid_front_photo');
                $name = time() ."nid_front_". $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/farmers/', $name);
                $nidFrontPhoto = 'uploads/farmers/' . $name;
            }else{
                $nidFrontPhoto = NULL;
            }


            if ($request->file('nid_back_photo')) {
                $file = $request->file('nid_back_photo');
                $name = time() . "nid_back_". $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/farmers/', $name);
                $nidBackPhoto = 'uploads/farmers/' . $name;
            }else{
                $nidBackPhoto = NULL;
            }


            if ($request->file('trade_license_photo')) {
                $file = $request->file('trade_license_photo');
                $name = time() ."trade_license_". $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/farmers/', $name);
                $nidTradeLicensePhoto = 'uploads/farmers/' . $name;
            }else{
                $nidTradeLicensePhoto = NULL;
            }

            // $columns = Schema::getColumnListing('userinfos');
            // return $columns;
            
            
            if ($request->file('profile_image')) {
                $file = $request->file('profile_image');
                $name = time() . "profile_". $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/farmers/', $name);
                $path = 'uploads/farmers/' . $name;
            }else{
                $path = NULL;
            }

            $user = new User();
            $user->role = 'farmer';
            $user->full_name = $request->full_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = bcrypt($request->password);
            $user->status = 'Active';
            $user->image_path = $path;
            $user->save();

            $info = new Userinfo();
            $info->user_id = $user->id;
            // $info->farmercategory_id = $request->farmercategory_id;
            // $info->farmersubcategory_id = $request->farmersubcategory_id;
            $info->businees_location = $request->businees_location;
            $info->businees_address = $request->businees_address;
            $info->nid_passport = $request->nid_passport;
            $info->nid_front_photo = $nidFrontPhoto;
            $info->nid_back_photo = $nidBackPhoto;
            $info->trade_license_photo = $nidTradeLicensePhoto;
            $info->save();

            if($request->has('categories'))
            {   
                $categories = explode(",",$request->categories);
                //return $categories;
                $user->farmercategories()->attach($categories);
            }

            DB::commit();

            $data = array('user'=>$user->load('farmercategories'), 'info'=>$info);

            return response()->json(['status'=>true, 'message'=>'Successfully Signup', 'data'=>$data]);


        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function farmerSignin(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'login' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $login = $request->input('login');
            $password = $request->input('password');

            $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            $user = User::where('email',$login)->orWhere('phone',$login)->first();
            
            if(!$user || $user->role != 'farmer')
            {
                return response()->json(['status'=>false, 'message'=>'Email/Phone or Password Invalid', 'token'=>"", 'user'=>new \stdClass()],403);
            }
            
            if($user->status == 'Inactive'){
                return response()->json(['status'=>false, 'message'=>'Sorry you are not active user', 'token'=>"", 'user'=>new \stdClass()],403);
            }

            if (Auth::attempt([$fieldType => $login, 'password' => $password])) {
                $token = $user->createToken('MyApp')->plainTextToken;
                return response()->json(['status'=>true,'message'=>'Successfully Logged IN', 'token'=>$token, 'user'=>$user]);
            }

            return response()->json(['status'=>false,'message'=>"Invalid Email/Phone or Password", 'token'=>"", 'user'=>new \stdClass()],401);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function farmerSignOut(Request $request)
    {
        try
        {
            auth()->user()->tokens()->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully Logged Out']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function units(Request $request)
    {
        try
        {
            $query = Farmerunit::query();
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('unit_name', 'LIKE', "%{$search}%")->orWhere('unit_name_bn', 'LIKE', "%{$search}%");
            }
            $data = $query->get();
            return response()->json(['status'=>count($data) > 0, 'data'=>$data]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function saveItem(Request $request)
    {
        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), [
                'item_name' => 'required|string|max:50',
                'item_name_bn' => 'required|string|max:50',
                'farmerunit_id' => 'required|integer|exists:farmerunits,id',
                // 'farmercategory_id' => 'required|integer|exists:farmercategories,id',
                // 'farmersubcategory_id' => 'nullable|integer|exists:farmersubcategories,id',
                'categories' => 'required',
                'subcategories' => 'nullable',
                'price' => 'required|numeric',
                'discount' => 'nullable|numeric',
                'stock_qty' => 'required|numeric',
                'description' => 'required',
                'featured_image' => 'required|image',
                'status' => 'required|in:Active,Inactive',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
                'delivery_charge' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'data' => $validator->errors()
                ], 422);
            }

            // Featured Image Upload
            $file = $request->file('featured_image');
            $name = time() . user()->id . '-' . $file->getClientOriginalName();
            $file->move(public_path('uploads/items'), $name);
            $featuredImage = 'uploads/items/' . $name;

            // Save Item
            $item = Farmeritem::create([
                'user_id' => user()->id,
                'farmerunit_id' => $request->farmerunit_id,
                // 'farmercategory_id' => $request->farmercategory_id,
                // 'farmersubcategory_id' => $request->farmersubcategory_id,
                'item_name' => $request->item_name,
                'item_name_bn' => $request->item_name_bn,
                'price' => $request->price,
                'discount' => $request->discount,
                'stock_qty' => $request->stock_qty,
                'description' => $request->description,
                'hit_count' => 0,
                'featured_image' => $featuredImage,
                'delivery_charge' => $request->delivery_charge,
                'status' => $request->status, 
            ]);

            // Multiple Images Upload
            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $image) {

                    $imageName = time() . '-' . $item->id . '-' . $image->getClientOriginalName();
                    $image->move(public_path('uploads/items/images'), $imageName);

                    $item->images()->create([
                        'image_path' => 'uploads/items/images/' . $imageName
                    ]);
                }
            }


            $item->farmercategories()->attach($request->categories);

            if($request->has('subcategories'))
            {
                $item->farmersubcategories()->attach($request->subcategories);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'item_id' => $item->id,
                'message' => 'Successfully an item has been added',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function itemLists(Request $request)
    {
        try {

            $query = Farmeritem::query();

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('item_name', 'LIKE', "%{$search}%")->orWhere('item_name_bn', 'LIKE', "%{$search}%");
            }

            if($request->has('status'))
            {
                $query->where('status',$request->status);
            }

            // if($request->has('category_id'))
            // {
            //     $query->where('farmercategory_id',$request->category_id);
            // }

            // if($request->has('subcategory_id'))
            // {
            //     $query->where('farmersubcategory_id',$request->subcategory_id);
            // }

            if(user()->role == 'farmer')
            {
                $query->where('user_id',user()->id);
            }

            if(user()->role != 'farmer')
            {
                $query->where('status','Active');
            }

            if($request->has('best_deal') && $request->best_deal == 1)
            {
                $query->whereNotNull('discount');
            }

            if($request->has('popular_product') && $request->popular_product == 1)
            {
                $query->orderBy('hit_count', 'desc');
            }

            if($request->has('farmer_id'))
            {
                $query->where('user_id',$request->farmer_id);
            }


            if ($request->is_paginate == 1) {

                $per_page = $request->per_page ?? 10;

                $data = $query->with('farmercategories','farmersubcategories','farmerunit','images')->latest()->paginate($per_page);

            } else {

                $data = $query->with('farmercategories','farmersubcategories','farmerunit','images')->latest()->get();
            }

            return response()->json([
                'status' => true,
                'data'   => $data
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function itemDetails($id) 
    {
        try
        {
            $data = Farmeritem::with('farmercategories','farmersubcategories','farmerunit','images','user.userinfo')->findorfail($id);
            return response()->json(['status'=>true, 'data'=>$data]);
        }catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateItem(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $item = Farmeritem::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'item_name' => 'required|string|max:50',
                'item_name_bn' => 'required|string|max:50',
                'farmerunit_id' => 'required|integer|exists:farmerunits,id',
                // 'farmercategory_id' => 'required|integer|exists:farmercategories,id',
                // 'farmersubcategory_id' => 'nullable|integer|exists:farmersubcategories,id',
                'categories' => 'required',
                'subcategories' => 'nullable',
                'price' => 'required|numeric',
                'discount' => 'nullable|numeric',
                'stock_qty' => 'required|numeric',
                'description' => 'required',
                'featured_image' => 'nullable|image',
                'status' => 'required|in:Active,Inactive',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
                'delivery_charge' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'data' => $validator->errors()
                ], 422);
            }

            /*
            ===============================
            FEATURED IMAGE UPDATE
            ===============================
            */
            if ($request->hasFile('featured_image')) {

                // Delete old file
                if ($item->featured_image && file_exists(public_path($item->featured_image))) {
                    unlink(public_path($item->featured_image));
                }

                $file = $request->file('featured_image');
                $name = time() . user()->id . '-' . $file->getClientOriginalName();
                $file->move(public_path('uploads/items'), $name);

                $item->featured_image = 'uploads/items/' . $name;
            }

            /*
            ===============================
            UPDATE MAIN DATA
            ===============================
            */

            $item->update([
                'farmerunit_id' => $request->farmerunit_id,
                // 'farmercategory_id' => $request->farmercategory_id,
                // 'farmersubcategory_id' => $request->farmersubcategory_id,
                'item_name' => $request->item_name,
                'item_name_bn' => $request->item_name_bn,
                'price' => $request->price,
                'discount' => $request->discount,
                'stock_qty' => $request->stock_qty,
                'description' => $request->description,
                'delivery_charge' => $request->delivery_charge,
                'status' => $request->status,
            ]);

            /*
            ===============================
            ADD NEW GALLERY IMAGES
            ===============================
            */

            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $image) {

                    $imageName = time() . '-' . $item->id . '-' . $image->getClientOriginalName();
                    $image->move(public_path('uploads/items/images'), $imageName);

                    $item->images()->create([
                        'image_path' => 'uploads/items/images/' . $imageName
                    ]);
                }
            }

            $item->farmercategories()->sync($request->categories);

            if($request->has('subcategories'))
            {
                $item->farmersubcategories()->sync($request->subcategories);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'item_id' => intval($item->id),
                'message' => 'Item updated successfully'
            ]);

        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteItem($id)
    {
        DB::beginTransaction();

        try {

            $item = Farmeritem::with('images')->findOrFail($id);

            /*
            ===============================
            DELETE FEATURED IMAGE FILE
            ===============================
            */
            if ($item->featured_image && file_exists(public_path($item->featured_image))) {
                unlink(public_path($item->featured_image));
            }

            /*
            ===============================
            DELETE GALLERY IMAGES FILES
            ===============================
            */
            foreach ($item->images as $image) {

                if ($image->image_path && file_exists(public_path($image->image_path))) {
                    unlink(public_path($image->image_path));
                }

                $image->delete();
            }

            $item->farmercategories()->detach();
            $item->farmersubcategories()->detach();

            /*
            ===============================
            DELETE ITEM
            ===============================
            */
            $item->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Item deleted successfully'
            ]);

        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function deleteItemImage($id)
    {
        try
        {
            $data = Farmerimage::findorfail($id);
            if(file_exists(public_path($data->image_path)))
            {
                unlink(public_path($data->image_path));
            }
            //unlink(public_path($data->image_path));
            $data->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the image has been deleted']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
    
    
    public function farmerDetails($id)
    {
        try
        {
            $farmer = User::with([
                'userinfo.farmercategory',
                'userinfo.farmersubcategory',
                'farmercategories'
            ])->findOrFail($id);
            
            return response()->json([
                'status' => true,
                'data' => $farmer
            ]);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }


    public function changePassword(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'new_password' => 'required',
                'confirm_password' => 'required|same:new_password',
                'current_password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $user = user();
            //$message = $user->changePassword($request,$user);

            if (!Hash::check($request->current_password, $user->password)) {
            
               return response()->json(['status'=>false, 'message'=>"The current password is incorrect"],400);
            } 

            $user->password = Hash::make($request->new_password);
            $user->update();

            return response()->json(['status'=>true, 'message'=>"Your password has been changed"],200);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function farmerProfileUpdate(Request $request)
    {   
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:50',
                'email' => 'nullable|email',
                'phone' => 'required',
                'farmercategory_id' => 'required|integer|exists:farmercategories,id',
                'farmersubcategory_id' => 'nullable|integer|exists:farmersubcategories,id',
                'address' => 'nullable',
                'profile_image' => 'nullable',
                'nid_passport' => 'required|string',
                'nid_front_photo' => 'nullable',
                'nid_back_photo' => 'nullable',
                'trade_license_photo' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $user = user();

            if ($request->file('profile_image')) {
                $file = $request->file('profile_image');
                $name = time() . "profile_". $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/farmers/', $name);
                unlink(public_path($user->image_path));
                $path = 'uploads/farmers/' . $name;
            }else{
                $path = $user->image_path;
            }

            

            $user->full_name = $request->full_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->image_path = $path;
            $user->update();

            $info = Userinfo::where('user_id',$user->id)->first();

            if ($request->file('nid_front_photo')) {
                $file = $request->file('nid_front_photo');
                $name = time() ."nid_front_". $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/farmers/', $name);
                unlink(public_path($info->nid_front_photo));
                $nidFrontPhoto = 'uploads/farmers/' . $name;
            }else{
                $nidFrontPhoto = $info->nid_front_photo;
            }


            if ($request->file('nid_back_photo')) {
                $file = $request->file('nid_back_photo');
                $name = time() . "nid_back_". $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/farmers/', $name);
                $nidBackPhoto = 'uploads/farmers/' . $name;
                unlink(public_path($info->nid_back_photo));
            }else{
                $nidBackPhoto = $info->nid_back_photo;
            }


            if ($request->file('trade_license_photo')) {
                $file = $request->file('trade_license_photo');
                $name = time() ."trade_license_". $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/farmers/', $name);
                $nidTradeLicensePhoto = 'uploads/farmers/' . $name;
                unlink(public_path($info->trade_license_photo));
            }else{
                $nidTradeLicensePhoto = $info->trade_license_photo;
            }

            $info->farmercategory_id = $request->farmercategory_id;
            $info->farmersubcategory_id = $request->farmersubcategory_id;
            $info->businees_location = $request->businees_location;
            $info->businees_address = $request->businees_address;
            $info->nid_passport = $request->nid_passport;
            $info->nid_front_photo = $nidFrontPhoto;
            $info->nid_back_photo = $nidBackPhoto;
            $info->trade_license_photo = $nidTradeLicensePhoto;
            $info->update();

            DB::commit();

            return response()->json(['status'=>true, 'message'=>'Successfully profile updated']);

        }catch(Exception $e){

            DB::rollback();
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function saveOrder(Request $request)
    {   
        date_default_timezone_set("Asia/Dhaka");
        DB::beginTransaction();
        try
        {
            $validator = Validator::make($request->all(), [
                //'farmeritem_id' => 'required|integer|exists:farmeritems,id',
                'data' => 'required|array|min:1',
                'payment_method' => 'required|in:cod,bkash,rocket,nagad',
                'order_total' => 'required|numeric',
                'order_type' => 'required|in:custom_collection,home_delivery',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $order = new Order();
            $order->user_id = user()->id;
            $order->delivery_charge = charge()->delivery_charge;
            $order->vat_tax = charge()->vat_tax;
            $order->date = date('Y-m-d');
            $order->time = date('h:i:s a');
            $order->month = date('F');
            $order->year = date('Y');
            $order->timestamp = time();
            $order->status = 'pending';
            $order->order_total = $request->order_total;
            $order->payment_method = $request->payment_method;
            $order->order_type = $request->order_type;
            $order->save();

            $odata = new Orderdetail();
            $odata->order_id = $order->id;
            $odata->items = json_encode($request->data);
            $odata->save();

            $items = json_decode($odata->items,true);

            $user_id = '';

            foreach($items as $item)
            {
                $user_id = $item['user_id'];

                $log = new Orderlog();
                $log->user_id = $user_id;
                $log->order_id = $order->id;
                $log->item_id = $item['id'];
                $log->qty = $item['qty'];
                $log->price = $item['price'];
                $log->unit_total = $item['unit_total'];
                $log->save();

            }    

            $notification = new Notification();
            $notification->user_id = $user_id;
            $notification->order_id = $order->id;
            $notification->title = "New Order Received";
            $notification->sub_title = "Order ID is #$order->id. Please check the order's details";
            $notification->status = 'unread';
            $notification->timestamp = time();
            $notification->save();

            DB::commit();

            return response()->json(['status'=>true, 'order_id'=>intval($order->id), 'message'=>'Successfully an order has been received we will contact to you soon']); 

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function orderLists(Request $request)
    {
        try {

            if(user()->role != 'farmer')
            {
                return response()->json(['status'=>false, 'message'=>'Invalid User', 'data'=>array()],400);
            }

            $query = Order::query();

            if($request->has('from_date'))
            {
                $query->whereDate('date','>=',$request->from_date);
            }

            if($request->has('to_date'))
            {
                $query->whereDate('date', '<=', $request->to_date);
            }


            if ($request->is_paginate == 1) {

                $per_page = $request->per_page ?? 10;

                $data = $query
                    ->whereHas('orderlogs', function ($q) {
                        $q->where('user_id', user()->id);
                    })
                    ->with(['orderlogs' => function ($q) {
                        $q->where('user_id', user()->id)
                          ->with('farmeritem');
                    }])
                    ->latest()
                    ->paginate($per_page);

            } else {

                $data = $query
                    ->whereHas('orderlogs', function ($q) {
                        $q->where('user_id', user()->id);
                    })
                    ->with(['orderlogs' => function ($q) {
                        $q->where('user_id', user()->id)
                          ->with('farmeritem.farmerunit');
                    }])
                    ->latest()
                    ->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Data found',
                'data'   => $data
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function orderDetails($id)
    {
        try
        {
            $order = Order::with('orderlogs.farmeritem.farmerunit')->findorfail($id);
            return response()->json(['status'=>true, 'data'=>$order]); 
        }catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function orderStatusChange(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|integer|exists:orders,id',
                'status' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $user = user();
            if($user->role != 'farmer')
            {
                return response()->json(['status'=>false, 'order_id'=>0, 'message'=>'You are not allowed to change the stauts'],400);
            }

            $order = Order::findorfail($request->order_id);
            $order->status = $request->status;
            $order->update();

            return response()->json(['status'=>true, 'order_id'=>intval($order->id), 'message'=>"Successfully the order's status updated"]);

        }catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function orderDelete($id)
    {
        try
        {
            $order = Order::findorfail($id);
            $order->orderdetails()->delete();
            $order->orderlogs()->delete();
            $order->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the order has been deleted']);
        }catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function notificationLists()
    {
        try
        {
            $notifications = Notification::where('user_id',user()->id)->latest()->get();
            return response()->json(['status'=>count($notifications) > 0, 'data'=>$notifications]);
        }catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function notificationStatusRead(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'notification_id' => 'required|integer|exists:notifications,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $noty = Notification::findorfail($request->notification_id);
            $noty->status = 'read';
            $noty->save();

            return response()->json(['status'=>true, 'message'=>'Successfully updated']);

        }catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function farmerDashboard(Request $request)
    {
        try
        {   

            $user = user();

            if($user->role != 'farmer')
            {
                return response()->json(['status'=>false, 'message'=>'Invalid User'],400);
            }

            // $totalOrders = Orderlog::where('user_id',user()->id)->sum('unit_total');
            // $ids = Orderlog::where('user_id',user()->id)->pluck('order_id')->toArray();
            // $totalSold = Order::whereIn('id',$ids)->where('status','completed')->sum('order_total');
            // $totalDelivered = Order::whereIn('id',$ids)->where('status','deliverd')->count();
            // $totalPending = Order::whereIn('id',$ids)->where('status','pending')->count();
            // $todayOrders = Order::whereIn('id',$ids)->where('date',date('Y-m-d'))->count();
            // $thisMonthOrders = Order::whereIn('id',$ids)->where('month',date('F'))->count();

            $data = DB::table('orderlogs')
                    ->join('orders', 'orders.id', '=', 'orderlogs.order_id')
                    ->where('orderlogs.user_id', user()->id)
                    ->select(
                        DB::raw('CAST(SUM(orderlogs.unit_total) AS CHAR) as totalOrders'),
                        DB::raw("CAST(SUM(CASE WHEN orders.status = 'completed' THEN orders.order_total ELSE 0 END) AS CHAR) as totalSold"),
                        DB::raw("CAST(COUNT(CASE WHEN orders.status = 'deliverd' THEN 1 END) AS CHAR) as totalDelivered"),
                        DB::raw("CAST(COUNT(CASE WHEN orders.status = 'pending' THEN 1 END) AS CHAR) as totalPending"),
                        DB::raw("CAST(COUNT(CASE WHEN orders.date = CURDATE() THEN 1 END) AS CHAR) as todayOrders"),
                        DB::raw("CAST(COUNT(CASE WHEN orders.month = '".date('F')."' THEN 1 END) AS CHAR) as thisMonthOrders")
                    )
                    ->first();

           return response()->json(['status'=>true, 'data'=>$data]);


        }catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function homePageItems(Request $request)
    {
        try
        {
            // Best deal items (discounted)
            $bestDell = Farmeritem::with([
                    'farmercategories',
                    'farmersubcategories',
                    'farmerunit',
                    'images',
                ])->whereNotNull('discount')
                ->where('status', 'Active')
                ->latest()
                ->limit(9)
                ->get(); // note: ->get() missing before

            // Best selling items (one-to-one orderlog)
            $bestSell = Farmeritem::with([
                    'farmercategories',
                    'farmersubcategories',
                    'farmerunit',
                    'images'
                    //'orderlog'
                ])
                ->get()
                ->transform(function ($item) {
                    // total sold (unit_total from orderlog)
                    $item->total_sold = $item->orderlog ? (string) $item->orderlog->unit_total : '0';
                    return $item;
                })
                ->sortByDesc('total_sold') // top selling first
                ->take(9)
                ->values();

            $popularProducts = Farmeritem::with([
                    'farmercategories',
                    'farmersubcategories',
                    'farmerunit',
                    'images'
                    //'orderlog'
                ])->orderBy('hit_count', 'desc')
                ->limit(4)
                ->get();

            $farmers = User::where('role', 'farmer')
                ->whereHas('farmeritems')
                ->limit(9)
                ->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'best_deal' => $bestDell,
                    'best_sell' => $bestSell,
                    'popularProducts' => $popularProducts,
                    'farmers' => $farmers
                ]
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function userDetails()
    {
        try
        {   
            if(user()->role != 'user')
            {
                return response()->json(['status'=>false, 'data'=>new \stdClass()],400);
            }
            $user = User::with('userinfo')->findorfail(user()->id);
            return response()->json(['status'=>true, 'data'=>$user]);
        }catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function saveItemRate(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                //'user_id' => 'required|integer|exists:users,id',
                'farmeritem_id' => 'required|integer|exists:farmeritems,id',
                'rating' => 'required|integer',
                'remarks' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            if(user()->role != 'user')
            {
                return response()->json(['status'=>false, 'message'=>'Invalid Request', 'data'=>new \stdClass()],400);
            }

            $rate = new Productrating();
            $rate->user_id = user()->id;
            $rate->farmeritem_id = $request->farmeritem_id;
            $rate->rating = $request->rating;
            $rate->remarks = $request->remarks;
            $rate->save();

            return response()->json(['status'=>true, 'message'=>'Successfully your review has been taken', 'data'=>$rate]);

        }catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function itemRateLists(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'farmeritem_id' => 'required|integer|exists:farmeritems,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            $items = Productrating::where('farmeritem_id',$request->farmeritem_id)->latest()->get();

            return response()->json(['status'=>count($items)>0, 'data'=>$items]);
        }catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function searchItems(Request $request)
    {
        try {

            $query = Farmeritem::query();

            // item name search
            if ($request->filled('item_name')) {
                $query->where(function ($q) use ($request) {
                    $q->where('item_name', 'LIKE', '%'.$request->item_name.'%')
                      ->orWhere('item_name_bn', 'LIKE', '%'.$request->item_name.'%');
                });
            }

            // stock search
            if ($request->filled('stock_qty')) {
                $query->where('stock_qty', '>=', $request->stock_qty);
            }

            // category search (many to many)
            if ($request->filled('farmercategory_id')) {
                $query->whereHas('farmercategories', function ($q) use ($request) {
                    $q->where('farmercategories.id', $request->farmercategory_id);
                });
            }

            // subcategory search (many to many)
            if ($request->filled('farmersubcategory_id')) {
                $query->whereHas('farmersubcategories', function ($q) use ($request) {
                    $q->where('farmersubcategories.id', $request->farmersubcategory_id);
                });
            }

            // relation load
            $query->with([
                'farmercategories',
                'farmersubcategories',
                'farmerunit',
                'images'
            ])->latest();

            // paginate option
            if ($request->is_paginate == 1) {

                $per_page = $request->per_page ?? 10;

                $data = $query->paginate($per_page);

            } else {

                $data = $query->get();
            }

            return response()->json([
                'status' => true,
                'data'   => $data
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // public function searchFarmer(Request $request)
    // {
    //     try
    //     {   

    //         $validator = Validator::make($request->all(), [
    //             //'user_id' => 'required|integer|exists:users,id',
    //             'lat_one' => 'required|numeric',
    //             'lon_one' => 'required|numeric',
    //             'lat_two' => 'nullable|numeric',
    //             'lon_two' => 'nullable|numeric',
    //             'radius'  => 'nullable|numeric',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => false, 
    //                 'message' => 'Please fill all requirement fields', 
    //                 'data' => $validator->errors()
    //             ], 422);  
    //         }


    //         if($request->filled('lat_one') && $request->filled('lon_one'))
    //         {
    //             if(empty($request->radius))
    //             {
    //                 return response()->json(['status'=>false, 'message'=>'Radius field is required', 'total'=>0, 'data'=>array()],422);
    //             }

    //             $lat1 = $request->lat_one;
    //             $lon1 = $request->lon_one;
    //             $radius = $request->radius;

    //             // Haversine formula
    //             $ids = Userinfo::select('*')
    //                 ->selectRaw("
    //                     ( 6371 * acos( 
    //                         cos( radians(?) ) 
    //                         * cos( radians( SUBSTRING_INDEX(businees_location, ',', 1) ) ) 
    //                         * cos( radians( SUBSTRING_INDEX(businees_location, ',', -1) ) - radians(?) ) 
    //                         + sin( radians(?) ) 
    //                         * sin( radians( SUBSTRING_INDEX(businees_location, ',', 1) ) ) 
    //                     ) ) AS distance
    //                 ", [$lat1, $lon1, $lat1])
    //                 ->havingRaw('distance <= ?', [$radius])
    //                 ->orderBy('distance', 'asc')
    //                 ->pluck('user_id')
    //                 ->toArray();

    //             $users = User::with('userinfo')->whereIn('id',$ids)->where('status','Active')->get();

    //             return response()->json(['status'=>count($users) > 0, 'message'=>'Data Found', 'total'=>count($users), 'data'=>$users]);

    //         }

            
    //     }catch (Exception $e) {

    //         return response()->json([
    //             'status'  => false,
    //             'code'    => $e->getCode(),
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function searchFarmer(Request $request)
    {
        try
        {   
            $validator = Validator::make($request->all(), [
                'lat_one' => 'required|numeric',
                'lon_one' => 'required|numeric',
                'lat_two' => 'nullable|numeric',
                'lon_two' => 'nullable|numeric',
                'radius'  => 'required|numeric',
                'category_id' => 'nullable|integer|exists:farmercategories,id',
                'is_paginate' => 'required|in:0,1',
                'per_page' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all required fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

            if($request->filled('lat_one') && $request->filled('lon_one') && $request->filled('lat_two') && $request->filled('lon_two'))
            {
                $latMin = min($request->lat_one, $request->lat_two);
                $latMax = max($request->lat_one, $request->lat_two);
                $lonMin = min($request->lon_one, $request->lon_two);
                $lonMax = max($request->lon_one, $request->lon_two);

                // Base query: bounding box filter
                $query = User::with('userinfo')->whereHas('userinfo', function($q) use ($latMin, $latMax, $lonMin, $lonMax){
                    $q->whereRaw("CAST(SUBSTRING_INDEX(businees_location, ',', 1) AS DECIMAL(10,7)) BETWEEN ? AND ?", [$latMin, $latMax])
                      ->whereRaw("CAST(SUBSTRING_INDEX(businees_location, ',', -1) AS DECIMAL(10,7)) BETWEEN ? AND ?", [$lonMin, $lonMax]);
                })->where('status','Active');

                // Category filter
                if ($request->filled('category_id')) {
                    $query->whereHas('farmercategories', function($q) use ($request) {
                        $q->where('farmercategory_id', $request->category_id);
                    });
                }

                // Pagination
                if ($request->is_paginate == 1) {
                    $perPage = $request->per_page ?? 10;
                    $users = $query->paginate($perPage);
                } else {
                    $users = $query->get();
                }

                return response()->json([
                    'status' => $users->count() > 0,
                    'message' => $users->count() > 0?"Data Found":"No data found",
                    'total' => $users->count(),
                    'data' => $users
                ]);
            }elseif($request->filled('lat_one') && $request->filled('lon_one')){
                    $lat1 = $request->lat_one;
                    $lon1 = $request->lon_one;
                    $radius = $request->radius;

                    if(empty($request->radius))
                    {
                        return response()->json(['status'=>false, 'message'=>'Radius field is required', 'total'=>0, 'data'=>array()],422);
                    }

                    // Haversine formula
                    $ids = Userinfo::select('*')
                        ->selectRaw("
                            ( 6371 * acos( 
                                cos( radians(?) ) 
                                * cos( radians( SUBSTRING_INDEX(businees_location, ',', 1) ) ) 
                                * cos( radians( SUBSTRING_INDEX(businees_location, ',', -1) ) - radians(?) ) 
                                + sin( radians(?) ) 
                                * sin( radians( SUBSTRING_INDEX(businees_location, ',', 1) ) ) 
                            ) ) AS distance
                        ", [$lat1, $lon1, $lat1])
                        ->havingRaw('distance <= ?', [$radius])
                        ->orderBy('distance', 'asc')
                        ->pluck('user_id')
                        ->toArray();

                    // Base query
                    $query = User::with('userinfo')->whereIn('id', $ids)->where('status','Active');

                    // Category filter
                    if ($request->filled('category_id')) {
                        $query->whereHas('farmercategories', function($q) use ($request) {
                            $q->where('farmercategory_id', $request->category_id);
                        });
                    }

                    // Pagination
                    if ($request->is_paginate == 1) {
                        $perPage = $request->per_page ?? 10;
                        $users = $query->paginate($perPage);
                    } else {
                        $users = $query->get();
                    }

                    return response()->json([
                        'status' => $users->count() > 0,
                        'message' => $users->count() > 0?"Data Found":"No data found",
                        'total' => $users->count(),
                        'data' => $users
                    ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    } 
     

    // public function searchFarmer(Request $request)
    // {
    //     try {   
    //         $validator = Validator::make($request->all(), [
    //             'lat_one' => 'required|numeric',
    //             'lon_one' => 'required|numeric',
    //             'radius'  => 'required|numeric', // radius km e
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => false, 
    //                 'message' => 'Please fill all requirement fields', 
    //                 'data' => $validator->errors()
    //             ], 422);  
    //         }

    //         $lat1 = $request->lat_one;
    //         $lon1 = $request->lon_one;
    //         $radius = $request->radius;

    //         // Haversine formula
    //         $locations = Userinfo::select('*')
    //             ->selectRaw("
    //                 ( 6371 * acos( 
    //                     cos( radians(?) ) 
    //                     * cos( radians( SUBSTRING_INDEX(businees_location, ',', 1) ) ) 
    //                     * cos( radians( SUBSTRING_INDEX(businees_location, ',', -1) ) - radians(?) ) 
    //                     + sin( radians(?) ) 
    //                     * sin( radians( SUBSTRING_INDEX(businees_location, ',', 1) ) ) 
    //                 ) ) AS distance
    //             ", [$lat1, $lon1, $lat1])
    //             ->havingRaw('distance <= ?', [$radius])
    //             ->orderBy('distance', 'asc')
    //             ->get();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Farmers found within radius',
    //             'data' => $locations
    //         ]);

    //     } catch (Exception $e) {

    //         return response()->json([
    //             'status'  => false,
    //             'code'    => $e->getCode(),
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
