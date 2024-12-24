@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Members</h3>
            </div>               
            <a href="{{route('member.create')}}" class="btn btn-primary">Tambah Member</a>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Id Member</th>
                            <th>Nama</th>
                            <th>Mulai Member</th>
                            <th>Akhir Member</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($members as $p)
                        <tr>
                            <td>{{$p->id_member}}</td>
                            <td>{{$p->nama}}</td>
                            <td>{{$p->mulai_member}}</td>
                            <td>{{$p->akhir_member}}</td>
                            <td>
                                <form action="{{ route('member.destroy', $p->id_member) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <a href="{{route('member.edit',$p->id_member)}}" class="btn btn-warning">Edit</a> | 
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Id Member</th>
                            <th>Nama</th>
                            <th>Mulai Member</th>
                            <th>Akhir Member</th>
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
