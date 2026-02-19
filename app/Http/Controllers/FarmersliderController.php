<?php

namespace App\Http\Controllers;

use App\Models\Farmerslider;
use Illuminate\Http\Request;
use DataTables;

class FarmersliderController extends Controller
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

            $sliders = Farmerslider::latest();

            return Datatables::of($sliders)
                ->addIndexColumn()


                ->addColumn('image', function ($row) {
                    $src = url('/')."/".$row->image;
                    return '<img style="width: 60px; height: 60px;" class="img-fluid" src="'.$src.'" />';
                })

                ->addColumn('action', function ($row) {

                    $editUrl = route('farmersliders.show', $row->id);

                    return '
                        <a href="' . $editUrl . '" 
                           class="btn btn-primary btn-sm action-button edit-category" 
                           data-id="' . $row->id . '">
                            <i class="fa fa-edit"></i>
                        </a>

                        <a href="#" 
                           class="btn btn-danger btn-sm delete-category action-button" 
                           data-id="' . $row->id . '">
                            <i class="fa fa-trash"></i>
                        </a>
                    ';
                })

                ->rawColumns(['image', 'action'])
                ->make(true);
        }

        return view('sliders.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('sliders.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try
        {   
            if ($request->file('image')) {
                $file = $request->file('image');
                $name = time() . user()->id . $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/sliders/', $name);
                $path = 'uploads/sliders/' . $name;
            }
            $slider = new Farmerslider();
            $slider->user_id = user()->id;
            $slider->image = $path;
            $slider->save();

            $notification=array(
                'messege'=>"Successfully a slider has been added",
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
     * @param  \App\Models\Farmerslider  $farmerslider
     * @return \Illuminate\Http\Response
     */
    public function show(Farmerslider $farmerslider)
    {
        return view('sliders.edit', compact('farmerslider'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Farmerslider  $farmerslider
     * @return \Illuminate\Http\Response
     */
    public function edit(Farmerslider $farmerslider)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Farmerslider  $farmerslider
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Farmerslider $farmerslider)
    {
        try
        {   
            if ($request->file('image')) {
                $file = $request->file('image');
                $name = time() . user()->id . $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/sliders/', $name);
                unlink(public_path($farmerslider->image));
                $path = 'uploads/sliders/' . $name;
            }else{
                $path = $farmerslider->image;
            }

            $farmerslider->image = $path;
            $farmerslider->update();

            $notification=array(
                'messege'=>"Successfully the slider has been updated",
                'alert-type'=>"success",
            );

            return redirect('/farmersliders')->with($notification);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Farmerslider  $farmerslider
     * @return \Illuminate\Http\Response
     */
    public function destroy(Farmerslider $farmerslider)
    {
        try
        {
            unlink(public_path($farmerslider));
            $farmerslider->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the slider has been deleted']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
}
