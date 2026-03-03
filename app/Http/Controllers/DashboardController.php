<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class DashboardController extends Controller
{   
     public function __construct()
    {
        $this->middleware('auth_check'); 
    }
    public function Dashboard()
    {
    	try
    	{   
            $data = DB::table('orderlogs')
                    ->join('orders', 'orders.id', '=', 'orderlogs.order_id')
                    ->select(
                        DB::raw('CAST(SUM(orderlogs.unit_total) AS CHAR) as totalOrders'),

                        DB::raw("CAST(SUM(CASE 
                            WHEN orders.status = 'completed' 
                            THEN orders.order_total 
                            ELSE 0 END) AS CHAR) as totalSold"),

                        DB::raw("CAST(COUNT(CASE 
                            WHEN orders.status = 'deliverd' 
                            THEN 1 END) AS CHAR) as totalDelivered"),

                        DB::raw("CAST(COUNT(CASE 
                            WHEN orders.status = 'pending' 
                            THEN 1 END) AS CHAR) as totalPending"),

                        DB::raw("CAST(COUNT(CASE 
                            WHEN orders.date = CURDATE() 
                            THEN 1 END) AS CHAR) as todayOrders"),

                        DB::raw("CAST(COUNT(CASE 
                            WHEN orders.month = '".date('F')."' 
                            THEN 1 END) AS CHAR) as thisMonthOrders"),

                        DB::raw("(SELECT COUNT(*) FROM users WHERE role = 'farmer') as totalFarmers"),

                        DB::raw("(SELECT COUNT(*) FROM users WHERE role = 'farmer' AND status = 'Active') as totalActiveFarmers")
                    )
                    ->first();

    		return view('layouts.app', compact('data'));

    	}catch(Exception $e){
                  
                $message = $e->getMessage();
      
                $code = $e->getCode();       
      
                $string = $e->__toString();       
                return response()->json(['message'=>$message, 'execption_code'=>$code, 'execption_string'=>$string]);
                exit;
        }
    }
}
