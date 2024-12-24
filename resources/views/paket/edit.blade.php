@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Paket</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{route('paket.update',$paket->id_paket)}}" method="post">
                @method('PUT')
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="jam">Jam</label>
                        <input type="text" class="form-control" id="jam" value='{{$paket->jam}}' name='jam'>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga</label>
                        <input type="text" class="form-control" id="harga" value='{{$paket->harga}}' name='harga'>
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
