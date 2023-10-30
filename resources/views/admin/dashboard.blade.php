@extends('admin.layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Dashboard</h1>
                </div>
                <div class="col-sm-6">
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            @include('admin.message')
            <div class="row">
                <div class="col-lg-2 col-6 text-center">
                    <div class="small-box card">
                        <div class="inner">
                            <h3>{{ $totalCategory }}</h3>
                            <p>Total Category</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="{{ route('categories.index') }}" class="small-box-footer text-dark">More info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-6  text-center">
                    <div class="small-box card">
                        <div class="inner">
                            <h3>{{ $totalSubCategory }}</h3>
                            <p>Total Sub Category</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="{{ route('sub-categories.index') }}" class="small-box-footer text-dark">More info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-6  text-center">
                    <div class="small-box card">
                        <div class="inner">
                            <h3>{{ $totalBrands }}</h3>
                            <p>Total Brands</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="{{ route('brands.index') }}" class="small-box-footer text-dark">More info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-6  text-center">
                    <div class="small-box card">
                        <div class="inner">
                            <h3>{{ $totalProducts }}</h3>
                            <p>Total Products</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="{{ route('products.index') }}" class="small-box-footer text-dark">More info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-6  text-center">
                    <div class="small-box card">
                        <div class="inner">
                            <h3>{{ $totalCostumer }}</h3>
                            <p>Total Customers</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="{{ route('users.index') }}" class="small-box-footer text-dark">More info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-6  text-center">
                    <div class="small-box card">
                        <div class="inner">
                            <h3>{{ $totalOrders }}</h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="{{ route('orders.index') }}" class="small-box-footer text-dark">More info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6  text-center">
                    <div class="small-box card">
                        <div class="inner">
                            <h3>{{ number_format($totalSale, 2, ',', '.') }} €</h3>
                            <p>Total Sale</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="javascript:void(0);" class="small-box-footer">&nbsp;</a>
                    </div>
                </div>

                <div class="col-lg-3 col-6  text-center">
                    <div class="small-box card">
                        <div class="inner">
                            <h3>{{ number_format($saleThisMonth, 2, ',', '.') }} €</h3>
                            <p>Total Sale This Month</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="javascript:void(0);" class="small-box-footer">&nbsp;</a>
                    </div>
                </div>

                <div class="col-lg-3 col-6  text-center">
                    <div class="small-box card">
                        <div class="inner">
                            <h3>{{ number_format($saleLastMonth, 2, ',', '.') }} €</h3>
                            <p>Total Sale Last Month ({{ $lastMonth }})</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="javascript:void(0);" class="small-box-footer">&nbsp;</a>
                    </div>
                </div>

                <div class="col-lg-3 col-6  text-center">
                    <div class="small-box card">
                        <div class="inner">
                            <h3>{{ number_format($saleLastThirtyDays, 2, ',', '.') }} €</h3>
                            <p>Total Sale Last 30 Days</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="javascript:void(0);" class="small-box-footer">&nbsp;</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJs')
    <script>
        console.log('Hello')
    </script>
@endsection
