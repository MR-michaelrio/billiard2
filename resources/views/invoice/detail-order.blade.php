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
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>QTY</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($orderItems as $order)
                        <tr>
                            <td>{{ $order->product_name }}</td>
                            <td>{{ $order->price }}</td>
                            <td>{{ $order->quantity }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Order ID</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                        </tr>
                    </tfoot>
                </table>
                <a href="{{ route('print.strukorder', ['order_id' => $id]) }}" class="btn btn-primary">Print</a>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</div>
@endsection
