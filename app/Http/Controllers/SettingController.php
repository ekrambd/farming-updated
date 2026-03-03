<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PasswordChangeRequest;
use App\Models\User;
use Auth;
use Hash;

class SettingController extends Controller
{   

	public function __construct()
    {
        $this->middleware('auth_check');
    }
    
    public function changePassword()
    {
    	return view('settings.change_password');
    }

    public function passwordChange(PasswordChangeRequest $request)
    {
        try
        {
            $user = User::find(user()->id);
            //$message = $user->changePassword($request,$user);

            if (!Hash::check($request->current_password, $user->password)) {
            
	           $message = ['message'=>'The current password is incorrect.', 'type'=>'error'];
	        }

	        $user->password = Hash::make($request->new_password);
	        $user->update();

	        $message = ['message'=>'Your password has been changed', 'type'=>'success'];

            $notification=array(
	             'messege'=>$message['message'],
	             'alert-type'=>$message['type']
            );

            return Redirect()->back()->with($notification);


        }catch(Exception $e){
                  
                $message = $e->getMessage();
      
                $code = $e->getCode();       
      
                $string = $e->__toString();       
                return response()->json(['message'=>$message, 'execption_code'=>$code, 'execption_string'=>$string]);
                exit;
        }
    }
}
