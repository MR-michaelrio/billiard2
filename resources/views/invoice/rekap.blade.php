@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rekap</h3>
            </div>               
            <!-- /.card-header -->
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Id Member</th>
                            <th>Id Belanja</th>
                            <th>No Meja</th>
                            <th>Lama Waktu</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($invoices as $p)
                        <tr>
                            <td>{{ $p->id_player }}</td>
                            <td>{{ $p->id_belanja }}</td>
                            <td>{{ optional($p->rentalinvoice)->no_meja ?? 'N/A' }}</td>
                            <td>{{ optional($p->rentalinvoice)->lama_waktu ?? 'N/A' }}</td>
                            <td>{{ $p->created_at }}</td>
                            <td>
                                <a href="{{ route('bl.showrekap', $p->id) }}" class="btn btn-primary">Detail</a>
                                <a href="{{ route('print.receiptrekap', $p->id_rental ?? 0) }}" class="btn btn-primary">Print Struk</a>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Id Member</th>
                            <th>Id Belanja</th>
                            <th>No Meja</th>
                            <th>Lama Waktu</th>
                            <th>Tanggal</th>
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
