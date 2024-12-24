@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Barang</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{route('produk.update',$produk->id_produk)}}" method="post">
                @method('PUT')
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="nama">Nama Produk</label>
                        <input type="text" class="form-control" id="nama" value='{{$produk->nama_produk}}' name='nama_produk' placeholder="Nama">
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga</label>
                        <input type="text" class="form-control" id="harga" value='{{$produk->harga}}' name='harga' placeholder="10">
                    </div>
                    <div class="form-group">
                        <label for="qty">QTY</label>
                        <input type="number" class="form-control" name='qty' value='{{$produk->qty}}' id="qty" placeholder="1">
                    </div>  
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
