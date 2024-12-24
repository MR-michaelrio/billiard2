@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rekap Order Makanan dan Minuman</h3>
            </div>    
            <!-- /.card-header -->
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Account ID</th>
                            <th>Order ID</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($summarizedOrders as $order)
                        <tr>
                            <td>{{ $order['accountid'] }}</td>
                            <td>{{ $order['order_id'] }}</td>
                            <td>{{ number_format($order['total_price'], 0, ',', '.') }}</td>
                            <td>{{ $order['status'] }}</td>
                            <td>{{ $order['created'] }}</td>
                            <td><a href="{{route('rekap.detailorder',$order['order_id'])}}" class="btn btn-warning">Detail</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Account ID</th>
                            <th>Order ID</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Detail</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</div>
@endsection
