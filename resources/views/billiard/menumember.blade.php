@extends('layout.main')
@section('content')
<div class="row">
    <a href="{{ route('bl.memberlanjutan', $no_meja) }}" class="btn btn-block btn-primary btn-lg">Lanjutan</a>
    <a href="{{ route('bl.memberperwaktu', $no_meja) }}" class="btn btn-block btn-primary btn-lg">Per-Waktu</a>
</div>
@endsection
