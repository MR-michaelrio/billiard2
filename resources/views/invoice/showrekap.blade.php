@extends('layout.main')
@section('content')

@foreach($invoices as $p)
    @php
        $id_player = $p->id_player;
        $lama_waktu = $p->lama_waktu;
        $waktu_mulai = $p->waktu_mulai;
        $waktu_akhir = $p->waktu_akhir;
        $no_meja = $p->no_meja;
    @endphp
@endforeach

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <li>Id Member: {{ $id_player ?? 'N/A' }}</li>
                    <li>Lama Waktu: {{$lama_waktu ?? 'N/A' }}</li>
                    <li>Waktu Mulai: {{$waktu_mulai ?? 'N/A' }}</li>
                    <li>Waktu Akhir: {{$waktu_akhir ?? 'N/A' }}</li>
                    <li>No Meja: {{ $no_meja ?? 'N/A' }}</li>
                </h3>
            </div>               
            <!-- /.card-header -->
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Makanan dan Minuman</th>
                            <th>QTY</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($invoices as $p)
                        @if($p->id_table == 0)
                            <tr>
                                <td colspan=2 class="text-center">Tidak Ada Pembelian</td>
                            </tr>
                        @else
                            <tr>
                                <td>{{$p->product_name}}</td>
                                <td>{{$p->quantity}}</td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Makanan dan Minuman</th>
                            <th>QTY</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</div>
@endsection
