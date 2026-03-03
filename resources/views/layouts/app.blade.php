@extends('admin_master')
@section('content')

<div class="content-wrapper">

    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Dashboard</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <div class="row">

                <!-- Total Orders -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $data->totalOrders ?? 0 }}</h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Sold -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $data->totalSold ?? 0 }}</h3>
                            <p>Total Sold Amount</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Delivered -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $data->totalDelivered ?? 0 }}</h3>
                            <p>Total Delivered</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-checkmark"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Pending -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $data->totalPending ?? 0 }}</h3>
                            <p>Total Pending</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-clock"></i>
                        </div>
                    </div>
                </div>

                <!-- Today Orders -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $data->todayOrders ?? 0 }}</h3>
                            <p>Today Orders</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-calendar"></i>
                        </div>
                    </div>
                </div>

                <!-- This Month -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>{{ $data->thisMonthOrders ?? 0 }}</h3>
                            <p>This Month Orders</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-calendar"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Farmers -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-dark">
                        <div class="inner">
                            <h3>{{ $data->totalFarmers ?? 0 }}</h3>
                            <p>Total Farmers</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person"></i>
                        </div>
                    </div>
                </div>

                <!-- Active Farmers -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-teal">
                        <div class="inner">
                            <h3>{{ $data->totalActiveFarmers ?? 0 }}</h3>
                            <p>Active Farmers</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-stalker"></i>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

</div>

@endsection