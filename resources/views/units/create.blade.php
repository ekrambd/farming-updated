@extends('admin_master')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Add Unit</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{URL::to('/dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{URL::to('/farmerunits')}}">All Unit
                                </a></li>
                        <li class="breadcrumb-item active">Add Unit</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add Unit</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{route('farmerunits.store')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="unit_name">Unit Name <span class="required">*</span></label>
                                <input type="text" name="unit_name" class="form-control" id="unit_name"
                                    placeholder="Unit Name" required="" value="{{old('unit_name')}}">
                                @error('unit_name')
                                <span class="alert alert-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div> 

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="unit_name_bn">Unit Name BN <span class="required">*</span></label>
                                <input type="text" name="unit_name_bn" class="form-control" id="unit_name_bn"
                                    placeholder="Unit Name BN" required="" value="{{old('unit_name_bn')}}">
                                @error('unit_name_bn')
                                <span class="alert alert-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="status">Select Status <span class="required">*</span></label>
                                <select class="form-control select2bs4" name="status" id="status" required="">
                                    <option value="" selected="" disabled="">Select Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                                @error('status')
                                <span class="alert alert-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        
                        <div class="form-group w-100 px-2">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
            </form>
        </div>
    </section>
</div>
@endsection