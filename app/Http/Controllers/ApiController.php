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
                'farmercategory_id' => 'required|integer|exists:farmercategories,id',
                'farmersubcategory_id' => 'nullable|integer|exists:farmersubcategories,id',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'password' => 'required|string',
                'nid_passport' => 'required|numeric',
                'confirm_password' => 'required|string|same:password',
                'categories' => 'required|array|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Please fill all requirement fields', 
                    'data' => $validator->errors()
                ], 422);  
            }

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
            $user->profile_image = $path;
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
                $user->farmercategories()->attach($request->categories);
            }

            DB::commit();

            $data = array('user'=>$user->load('categories'), 'info'=>$info);

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
            
            if(!$user)
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
                'categories' => 'required|array|min:1',
                'subcategories' => 'nullable|array|min:1',
                'price' => 'required|numeric',
                'discount' => 'nullable|numeric',
                'stock_qty' => 'required|numeric',
                'description' => 'required',
                'featured_image' => 'required|image',
                'status' => 'required|in:Active,Inactive',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
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
                'featured_image' => $featuredImage,
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

            if($request->has('category_id'))
            {
                $query->where('farmercategory_id',$request->category_id);
            }

            if($request->has('subcategory_id'))
            {
                $query->where('farmersubcategory_id',$request->subcategory_id);
            }


            if ($request->is_paginate == 1) {

                $per_page = $request->per_page ?? 10;

                $data = $query->with('farmercategory','farmersubcategory','farmerunit')->where('user_id',user()->id)->latest()->paginate($per_page);

            } else {

                $data = $query->with('farmercategories','farmersubcategories','farmerunit')->where('user_id',user()->id)->latest()->get();
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
            $data = Farmeritem::with('farmercategories','farmersubcategories','farmerunit','images')->findorfail($id);
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
                'categories' => 'required|array|min:1',
                'subcategories' => 'nullable|array|min:1',
                'price' => 'required|numeric',
                'discount' => 'nullable|numeric',
                'stock_qty' => 'required|numeric',
                'description' => 'required',
                'featured_image' => 'nullable|image',
                'status' => 'required|in:Active,Inactive',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
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
                'userinfo.farmersubcategory'
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
            $order->save();

            $odata = new Orderdetail();
            $odata->order_id = $order->id;
            $odata->items = json_encode($request->data);
            $odata->save();

            $items = json_decode($odata->items,true);

            foreach($items as $item)
            {
                $notification = new Notification();
                $notification->user_id = $item['user_id'];
                $notification->order_id = $order->id;
                $notification->title = "New Order Received";
                $notification->sub_title = "Order ID is #$order->id. Please check the order's details";
                $notification->status = 'unread';
                $notification->time = time();
                $notification->save();

                $log = new Orderlog();
                $log->user_id = $item['user_id'];
                $log->order_id = $order->id;
                $log->item_id = $item['id'];
                $log->qty = $item['qty'];
                $log->price = $item['price'];
                $log->unit_total = $item['unit_total'];
                $log->save();

            }    

            

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

            $query = Order::query();

            // if ($request->has('search') && !empty($request->search)) {
            //     $search = $request->search;
            //     $query->where('category_name', 'LIKE', "%{$search}%")->orWhere('category_name_bn', 'LIKE', "%{$search}%");
            // }

            // $query->where('status', 'Active');


            // $query->with(['farmersubcategories' => function ($q) {
            //     $q->where('status', 'Active');
            // }]);

            if($request->has('from_date'))
            {
                $query->where('date','>=',$request->from_date);
            }

            if($request->has('to_date'))
            {
                $query->where('date', '<=', $request->to_date);
            }

            if($request->has('status'))
            {
                $query->where('status',$request->status);
            }

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

    public function orderDetails($id)
    {
        try
        {
            $order = Order::with('orderdetails')->findorfail($id);
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
            $order = Order::findorfail($request->order_id);
            $order->orderdetails()->delete();
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
            return response()->json(['status'=>count($notifications), 'data'=>$notifications]);
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

}
