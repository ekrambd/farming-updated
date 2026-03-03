@extends('admin_master')

@section('content')
<div class="content-wrapper">

    <!-- Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Farmer Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="{{URL::to('/dashboard')}}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">Farmer Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <section class="content">
        <div class="container-fluid">

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Farmer Information</h3>
                </div>

                <div class="card-body">
                    <div class="row">

                        <!-- Profile Image -->
                        <div class="col-md-4 text-center mb-3">
                            <img src="{{ $user->image_path ? asset($user->image_path) : 'https://via.placeholder.com/150' }}"
                                 class="img-fluid rounded"
                                 style="max-height:150px;">
                        </div>

                        <!-- Basic Info -->
                        <div class="col-md-8">
                            <p><strong>Full Name:</strong> {{ $user->full_name }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                        </div>

                        <!-- Business Address -->
                        <div class="col-md-12">
                            <p><strong>Business Address:</strong> {{ $user->userinfo->businees_location ?? 'N/A' }}</p>
                        </div>

                        <!-- NID -->
                        <div class="col-md-12">
                            <p><strong>NID / Passport:</strong> {{ $user->userinfo->nid_passport }}</p>
                        </div>

                        <!-- Images -->
                        <div class="col-md-4 text-center">
                            <label>NID Front</label><br>
                            <img src="{{ $user->userinfo->nid_front_photo ? asset($user->userinfo->nid_front_photo) : 'https://via.placeholder.com/150' }}"
                                 class="img-fluid rounded"
                                 style="max-height:150px;">
                        </div>

                        <div class="col-md-4 text-center">
                            <label>NID Back</label><br>
                            <img src="{{ $user->userinfo->nid_back_photo ? asset($user->userinfo->nid_back_photo) : 'https://via.placeholder.com/150' }}"
                                 class="img-fluid rounded"
                                 style="max-height:150px;">
                        </div>

                        <div class="col-md-4 text-center">
                            <label>Trade License</label><br>
                            <img src="{{ $user->userinfo->trade_license_photo ? asset($user->userinfo->trade_license_photo) : 'https://via.placeholder.com/150' }}"
                                 class="img-fluid rounded"
                                 style="max-height:150px;">
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection