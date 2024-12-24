@extends('layout.main')
@section('content')
<div class="row">
    <a href="{{ route('bl.nonmemberlanjutan', $no_meja) }}" class="btn btn-block btn-primary btn-lg">Lanjutan</a>
    <a href="{{ route('bl.nonmemberperwaktu', $no_meja) }}" class="btn btn-block btn-primary btn-lg">Per-Waktu</a>
</div>
@endsection
