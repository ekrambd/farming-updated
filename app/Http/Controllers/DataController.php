<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;
use App\Models\User;

class DataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth_check');
    }

    public function farmerLists(Request $request)
    {
    	if ($request->ajax()) {

            $farmers = User::with('userinfo')->where('role','farmer')->latest();

            return Datatables::of($farmers)
                ->addIndexColumn()

                ->addColumn('status', function ($row) {
                    $isActive = $row->status === 'Active';

                    return '
                        <label class="switch">
                            <input 
                                type="checkbox"
                                id="status-farmer-update"
                                class="' . ($isActive ? 'active-farmer' : 'decline-farmer') . '"
                                data-id="' . $row->id . '"
                                ' . ($isActive ? 'checked' : '') . '
                            >
                            <span class="slider round"></span>
                        </label>
                    ';
                })

                ->addColumn('action', function ($row) {

                    $farmerDetails = url('/farmer-details/'.$row->id);

                    return '
                        <a href="' . $farmerDetails . '" 
                           class="btn btn-primary btn-sm action-button edit-farmer" 
                           data-id="' . $row->id . '">
                            <i class="fa fa-eye"></i>
                        </a>

                        <a href="#" 
                           class="btn btn-danger btn-sm delete-farmer action-button" 
                           data-id="' . $row->id . '">
                            <i class="fa fa-trash"></i>
                        </a>
                    ';
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('farmers.index'); 
    }

    public function farmerDetails($id)
    {
    	try
    	{
    		$user = User::with('userinfo')->findorfail($id);
    		//return $user;
    		return view('farmers.profile', compact('user'));
    	}catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'code'    => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
