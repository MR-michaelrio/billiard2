@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Harga Paket</h3>
            </div>    
            <a href="{{route('paket.create')}}" class="btn btn-primary">Tambah Paket</a>           
            <!-- /.card-header -->
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Harga</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($paket as $p)
                        <tr>
                            <td>{{$p->jam}}</td>
                            <td>{{$p->harga}}</td>
                            <td>
                                <form action="{{ route('paket.destroy', $p->id_paket) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <a href="{{route('paket.edit',$p->id_paket)}}" class="btn btn-warning">Edit</a> | 
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Jenis</th>
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
