@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <h1>Meja {{$no_meja}}</h1>
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Tambah Waktu</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{route('bl.storenonmember2')}}" method="post">
                @csrf
                <input type="hidden" value="{{$no_meja}}" name="no_meja" class="form-control">
                <div class="card-body">
                    <div class="form-group">
                        <label for="lama">Lama Main</label>
                        <input type="time" class="form-control" name='lama_waktu' id="lama" value='00:00'>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection
