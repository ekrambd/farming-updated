<?php

namespace App\Http\Controllers;

use App\Models\Farmersubcategory;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSubcategoryRequest;
use App\Http\Requests\UpdateSubcategoryRequest;
use DataTables;

class FarmersubcategoryController extends Controller
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

            $subcategories = Farmersubcategory::latest();

            return Datatables::of($subcategories)
                ->addIndexColumn()


                ->addColumn('category', function ($row) {
                    return $row->farmercategory->category_name;
                })


                ->addColumn('status', function ($row) {
                    $isActive = $row->status === 'Active';

                    return '
                        <label class="switch">
                            <input 
                                type="checkbox"
                                id="status-subcategory-update"
                                class="' . ($isActive ? 'active-subcategory' : 'decline-subcategory') . '"
                                data-id="' . $row->id . '"
                                ' . ($isActive ? 'checked' : '') . '
                            >
                            <span class="slider round"></span>
                        </label>
                    ';
                })

                ->addColumn('action', function ($row) {

                    $editUrl = route('farmersubcategories.show', $row->id);

                    return '
                        <a href="' . $editUrl . '" 
                           class="btn btn-primary btn-sm action-button edit-subcategory" 
                           data-id="' . $row->id . '">
                            <i class="fa fa-edit"></i>
                        </a>

                        <a href="#" 
                           class="btn btn-danger btn-sm delete-subcategory action-button" 
                           data-id="' . $row->id . '">
                            <i class="fa fa-trash"></i>
                        </a>
                    ';
                })

                ->rawColumns(['status','action', 'category'])
                ->make(true);
        }

        return view('subcategories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('subcategories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSubcategoryRequest $request)
    {
        try
        {   

            if ($request->file('image')) {
                $file = $request->file('image');
                $name = time() . user()->id . $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/subcategories/', $name);
                $path = 'uploads/subcategories/' . $name;
            }else{
                $path = NULL;
            }

            $subcategory = new Farmersubcategory();
            $subcategory->user_id = user()->id;
            $subcategory->subcategory_name = $request->subcategory_name;
            $subcategory->subcategory_name_bn = $request->subcategory_name_bn;
            $subcategory->farmercategory_id = $request->farmercategory_id;
            $subcategory->status = $request->status;
            $subcategory->image = $path;
            $subcategory->save();

            $notification=array(
                'messege'=>"Successfully the subcategory has been updated",
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
     * @param  \App\Models\Farmersubcategory  $farmersubcategory
     * @return \Illuminate\Http\Response
     */
    public function show(Farmersubcategory $farmersubcategory)
    {   
        //return $farmersubcategory;
        return view('subcategories.edit', compact('farmersubcategory'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Farmersubcategory  $farmersubcategory
     * @return \Illuminate\Http\Response
     */
    public function edit(Farmersubcategory $farmersubcategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Farmersubcategory  $farmersubcategory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSubcategoryRequest $request, Farmersubcategory $farmersubcategory)
    {
        try
        {   

            if ($request->file('image')) {
                $file = $request->file('image');
                $name = time() . user()->id . $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/subcategories/', $name);
                if($farmersubcategory->image != NULL){
                    unlink(public_path($farmersubcategory->image));
                }
                $path = 'uploads/subcategories/' . $name;
            }else{
                $path = $farmersubcategory->image;
            }

            $subcategory = $farmersubcategory;
            $subcategory->subcategory_name = $request->subcategory_name;
            $subcategory->subcategory_name_bn = $request->subcategory_name_bn;
            $subcategory->farmercategory_id = $request->farmercategory_id;
            $subcategory->status = $request->status;
            $subcategory->image = $path;
            $subcategory->update();

            $notification=array(
                'messege'=>"Successfully the subcategory has been updated",
                'alert-type'=>"success",
            );

            return redirect('/farmersubcategories')->with($notification);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Farmersubcategory  $farmersubcategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(Farmersubcategory $farmersubcategory)
    {
        try
        {
            if($farmersubcategory->image != NULL){
                unlink(public_path($farmersubcategory->image));
            }
            $farmersubcategory->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the subcategory has been deleted']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
}
