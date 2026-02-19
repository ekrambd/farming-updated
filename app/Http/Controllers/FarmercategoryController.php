<?php

namespace App\Http\Controllers;

use App\Models\Farmercategory;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use DataTables;

class FarmercategoryController extends Controller
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

            $categories = Farmercategory::latest();

            return Datatables::of($categories)
                ->addIndexColumn()


                ->addColumn('image', function ($row) {
                    $src = url('/')."/".$row->image;
                    return '<img style="width: 60px; height: 60px;" class="img-fluid" src="'.$src.'" />';
                })

                ->addColumn('status', function ($row) {
                    $isActive = $row->status === 'Active';

                    return '
                        <label class="switch">
                            <input 
                                type="checkbox"
                                id="status-category-update"
                                class="' . ($isActive ? 'active-category' : 'decline-category') . '"
                                data-id="' . $row->id . '"
                                ' . ($isActive ? 'checked' : '') . '
                            >
                            <span class="slider round"></span>
                        </label>
                    ';
                })

                ->addColumn('action', function ($row) {

                    $editUrl = route('farmercategories.show', $row->id);

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

                ->rawColumns(['status', 'image', 'action'])
                ->make(true);
        }

        return view('categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryRequest $request)
    {
        try
        {
            if ($request->file('image')) {
                $file = $request->file('image');
                $name = time() . user()->id . $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/categories/', $name);
                $path = 'uploads/categories/' . $name;
            }
            $category = new Farmercategory();
            $category->user_id = user()->id;
            $category->category_name = $request->category_name;
            $category->category_name_bn = $request->category_name_bn;
            $category->status = $request->status;
            $category->image = $path;
            $category->save();

            $notification=array(
                'messege'=>"Successfully a category has been added",
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
     * @param  \App\Models\Farmercategory  $farmercategory
     * @return \Illuminate\Http\Response
     */
    public function show(Farmercategory $farmercategory)
    {
        return view('categories.edit', compact('farmercategory'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Farmercategory  $farmercategory
     * @return \Illuminate\Http\Response
     */
    public function edit(Farmercategory $farmercategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Farmercategory  $farmercategory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoryRequest $request, Farmercategory $farmercategory)
    {
        try
        {
            if ($request->file('image')) {
                $file = $request->file('image');
                $name = time() . user()->id . $file->getClientOriginalName();
                $file->move(public_path() . '/uploads/categories/', $name);
                $path = 'uploads/categories/' . $name;
            }else{
                $path = $farmercategory->image;
            }
            $category = $farmercategory;
            $category->category_name = $request->category_name;
            $category->category_name_bn = $request->category_name_bn;
            $category->status = $request->status;
            $category->image = $path;
            $category->update();

            $notification=array(
                'messege'=>"Successfully the category has been updated",
                'alert-type'=>"success",
            );

            return redirect('/farmercategories')->with($notification);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Farmercategory  $farmercategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(Farmercategory $farmercategory)
    {
        try
        {
            unlink(public_path($farmercategory->image));
            $farmercategory->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the category has been deleted']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }
}
