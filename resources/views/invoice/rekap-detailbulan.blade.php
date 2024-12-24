@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rekap Detail Bulanan</h3>
            </div>               
            <!-- /.card-header -->
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Pelanggan</th>
                            <th>ID Belanja</th>
                            <th>Harga Table</th>
                            <th>Harga Cafe</th>
                            <th>Lama Main</th>
                            <th>No Meja</th>
                            <th>Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($rekaps as $invoice)
                        <tr>
                            <td>{{ $invoice->created_at }}</td>
                            <td>{{ $invoice->id_player ?? '-' }}</td>
                            <td>{{ $invoice->id_belanja ?? '-' }}</td>
                            <td>{{ $invoice->mejatotal ?? '-' }}</td>
                            <td>{{ $invoice->harga_cafe ?? '-' }}</td>
                            <td>{{ $invoice->lama_waktu ?? '-' }}</td>
                            <td>{{ $invoice->no_meja ?? '-' }}</td>
                            <td>{{ $invoice->metode ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Pelanggan</th>
                            <th>ID Belanja</th>
                            <th>Harga Table</th>
                            <th>Harga Cafe</th>
                            <th>Lama Main</th>
                            <th>No Meja</th>
                            <th>Metode</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</div>
@endsection
