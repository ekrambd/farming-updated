<?php

namespace App\Http\Controllers;

use App\Models\Farmerunit;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use DataTables;

class FarmerunitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware('auth_check');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $units = Farmerunit::latest();

            return Datatables::of($units)
                ->addIndexColumn()

                ->addColumn('status', function ($row) {
                    $isActive = $row->status === 'Active';

                    return '
                        <label class="switch">
                            <input 
                                type="checkbox"
                                id="status-unit-update"
                                class="' . ($isActive ? 'active-unit' : 'decline-unit') . '"
                                data-id="' . $row->id . '"
                                ' . ($isActive ? 'checked' : '') . '
                            >
                            <span class="slider round"></span>
                        </label>
                    ';
                })

                ->addColumn('action', function ($row) {

                    $editUrl = route('farmerunits.show', $row->id);

                    return '
                        <a href="' . $editUrl . '" 
                           class="btn btn-primary btn-sm action-button edit-unit" 
                           data-id="' . $row->id . '">
                            <i class="fa fa-edit"></i>
                        </a>

                        <a href="#" 
                           class="btn btn-danger btn-sm delete-unit action-button" 
                           data-id="' . $row->id . '">
                            <i class="fa fa-trash"></i>
                        </a>
                    ';
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('units.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('units.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUnitRequest $request)
    {
        try
        {
            $unit = new Farmerunit();
            $unit->user_id = user()->id;
            $unit->unit_name = $request->unit_name;
            $unit->unit_name_bn = $request->unit_name_bn;
            $unit->status = $request->status;
            $unit->save();

            $notification=array(
                'messege'=>"Successfully an unit has been added",
                'alert-type'=>"success",
            );

            return redirect()->back()->with($notification);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Farmerunit  $farmerunit
     * @return \Illuminate\Http\Response
     */
    public function show(Farmerunit $farmerunit)
    {
        return view('units.edit', compact('farmerunit'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Farmerunit  $farmerunit
     * @return \Illuminate\Http\Response
     */
    public function edit(Farmerunit $farmerunit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Farmerunit  $farmerunit
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUnitRequest $request, Farmerunit $farmerunit)
    {
        try
        {   
            $unit = $farmerunit;
            $unit->unit_name = $request->unit_name;
            $unit->unit_name_bn = $request->unit_name_bn;
            $unit->status = $request->status;
            $unit->update();

            $notification=array(
                'messege'=>"Successfully the unit has been updated",
                'alert-type'=>"success",
            );

            return redirect('/farmerunits')->with($notification);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Farmerunit  $farmerunit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Farmerunit $farmerunit)
    {
        try
        {
            $farmerunit->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the unit has been deleted']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
}
