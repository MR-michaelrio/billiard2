@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Member</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{route('member.update',$Member->id_member)}}" method="post">
                @method('PUT')
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="nama">Nama Member</label>
                        <input type="text" class="form-control" id="nama" name='nama' value="{{$Member->nama}}" placeholder="Nama">
                    </div>
                    <div class="form-group">
                        <label for="no_telp">No Telp</label>
                        <input type="text" class="form-control" id="no_telp" name='no_telp' value="{{$Member->no_telp}}" placeholder="08.......">
                    </div>
                    <div class="form-group">
                        <label for="tanggal_lahir">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="tanggal_lahir" value="{{$Member->tanggal_lahir}}" name='tanggal_lahir'>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <textarea class="form-control" name="alamat" id="alamat" rows="3" placeholder="Enter ...">{{$Member->alamat}}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="mulai_member">Mulai Member</label>
                        <input type="date" class="form-control" id="mulai_member" value="{{$Member->mulai_member}}" name='mulai_member'>
                    </div>
                    <div class="form-group">
                        <label for="durasi">Durasi</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name='durasi' id="durasi" value="{{$Member->durasi}}" placeholder="1">
                            <div class="input-group-append">
                                <span class="input-group-text">Bulan</span>
                            </div>
                        </div>
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
