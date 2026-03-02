<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DataTables;

class DataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth_check');
    }

    public function farmerLists(Request $request)
    {
    	//
    }
}
