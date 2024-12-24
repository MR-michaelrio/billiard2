@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Stok Barang</h3>
            </div>               
            <a href="{{route('produk.create')}}" class="btn btn-primary">Tambah Produk</a>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="example2" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Id Produk</th>
                            <th>Nama Produk</th>
                            <th>QTY</th>
                            <th>Harga</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($produk as $p)
                        <tr>
                            <td>{{$p->id_produk}}</td>
                            <td>{{$p->nama_produk}}</td>
                            <td>{{$p->qty}}</td>
                            <td>{{$p->harga}}</td>
                            <td>
                                <form action="{{ route('produk.destroy', $p->id_produk) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <a href="{{route('produk.edit',$p->id_produk)}}" class="btn btn-warning">Edit</a> | 
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Id Produk</th>
                            <th>Nama Produk</th>
                            <th>QTY</th>
                            <th>Harga</th>
                            <th>Status</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</div>
@endsection
