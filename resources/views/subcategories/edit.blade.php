@extends('admin_master')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit SubCategory</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{URL::to('/dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{URL::to('/farmersubcategories')}}">All SubCategory
                                </a></li>
                        <li class="breadcrumb-item active">Edit SubCategory</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Edit SubCategory</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{route('farmersubcategories.update',$farmersubcategory->id)}}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="subcategory_name">SubCategory Name <span class="required">*</span></label>
                                <input type="text" name="subcategory_name" class="form-control" id="subcategory_name"
                                    placeholder="SubCategory Name" required="" value="{{old('subcategory_name',$farmersubcategory->subcategory_name)}}">
                                @error('subcategory_name')
                                <span class="alert alert-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div> 


                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="subcategory_name_bn">SubCategory Name (BN) <span class="required">*</span></label>
                                <input type="text" name="subcategory_name_bn" class="form-control" id="subcategory_name_bn"
                                    placeholder="SubCategory Name" required="" value="{{old('subcategory_name_bn',$farmersubcategory->subcategory_name_bn)}}">
                                @error('subcategory_name_bn')
                                <span class="alert alert-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="category_id">Select Category <span class="required">*</span></label>
                                <select class="form-control select2bs4" name="farmercategory_id" id="category_id" required="">
                                    <option value="" selected="" disabled="">Select Category</option>
                                    @foreach(categories() as $category)
                                     <option value="{{$category->id}}" <?php if($farmersubcategory->farmercategory_id === $category->id){echo "selected";} ?>>{{$category->category_name}}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <span class="alert alert-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="status">Select Status <span class="required">*</span></label>
                                <select class="form-control select2bs4" name="status" id="status" required="">
                                    <option value="" selected="" disabled="">Select Status</option>
                                    <option value="Active" <?php if($farmersubcategory->status == 'Active'){echo "selected";} ?>>Active</option>
                                    <option value="Inactive" <?php if($farmersubcategory->status == 'Inactive'){echo "selected";} ?>>Inactive</option>
                                </select>
                                @error('status')
                                <span class="alert alert-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        @if($farmersubcategory->image != NULL)

                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="image">Image</label>
                            <input name="image" type="file" id="image" accept="image/*" class="dropify" data-height="150" data-default-file="{{URL::to($farmersubcategory->image)}}"/> 
                            @error('image')
                            <span class="alert alert-danger">{{ $message }}</span>
                            @enderror
                          </div>
                        </div>
                        @else
                          <div class="col-md-12">
                          <div class="form-group">
                            <label for="image">Image</label>
                            <input name="image" type="file" id="image" accept="image/*" class="dropify" data-height="150"/> 
                            @error('image')
                            <span class="alert alert-danger">{{ $message }}</span>
                            @enderror
                          </div>
                        </div>

                        @endif

                        
                        <div class="form-group w-100 px-2">
                            <button type="submit" class="btn btn-success">Save Changes</button>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
            </form>
        </div>
    </section>
</div>
@endsection