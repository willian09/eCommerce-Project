@extends('admin.layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Orders</h1>
                </div>
                <div class="col-sm-6 text-right">
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            @include('admin.message')
            <div class="card">
                <form action="" method="GET">
                    <div class="card-header">
                        <div class="card-title">
                            <button type="button" onclick="window.location.href='{{ route('orders.index') }}'"
                                class="btn btn-default btn-sm">Reset</button>
                        </div>
                        <div class="card-tools">
                            <div class="input-group input-group" style="width: 250px;">
                                <input value="{{ Request::get('keyword') }}" type="text" name="keyword"
                                    class="form-control float-right" placeholder="Search">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th width="60">Order #</th>
                                <th class="text-center" style="vertical-align:middle">Customer</th>
                                <th class="text-center" style="vertical-align:middle">Email</th>
                                <th class="text-center" style="vertical-align:middle" width="100">Phone</th>
                                <th class="text-center" style="vertical-align:middle" width="100">Status</th>
                                <th class="text-center" style="vertical-align:middle" width="100">Amount</th>
                                <th class="text-center" style="vertical-align:middle" width="100">Date Purchased</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($orders->isNotEmpty())
                                @foreach ($orders as $order)
                                    <tr>
                                        <td class="text-center" style="vertical-align:middle"><a
                                                href="{{ route('orders.detail', [$order->id]) }}">{{ $order->id }}</a>
                                        </td>
                                        <td class="text-center" style="vertical-align:middle">{{ $order->name }}</td>
                                        <td class="text-center" style="vertical-align:middle">{{ $order->email }}</td>
                                        <td class="text-center" style="vertical-align:middle">{{ $order->mobile }}</td>
                                        <td class="text-center" style="vertical-align:middle">
                                            @if ($order->status == 'pending')
                                                <span class="badge bg-danger">Pending</span>
                                            @elseif ($order->status == 'shipped')
                                                <span class="badge bg-info">Shipped</span>
                                            @elseif ($order->status == 'delivered')
                                                <span class="badge bg-success">Delivered</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td class="text-center" style="vertical-align:middle">
                                            {{ number_format($order->grand_total, 2, ',', '.') }} €</td>
                                        <td class="text-center" style="vertical-align:middle">
                                            {{ Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5">Records not found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection