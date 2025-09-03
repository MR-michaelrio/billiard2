@extends('layout.main')
@section('content')
<div class="row">
    <a href="{{ route('bl.menunonmember', $no_meja) }}" class="btn btn-block btn-primary btn-lg">Non Member</a>
    <a href="{{ route('bl.tambahwaktu', $no_meja) }}" class="btn btn-block btn-primary btn-lg">Tambah Waktu</a>
    <button class="btn btn-block btn-primary btn-lg btn-stop" data-nomor-meja="{{ $no_meja }}">Stop</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Extract `lama_main` from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const lamaMain = urlParams.get('lama_main');

        // Add event listener for the stop button
        document.querySelectorAll('.btn-stop').forEach(button => {
            button.addEventListener('click', function () {
                const nomorMeja = this.getAttribute('data-nomor-meja');

                // Redirect to the stop page with nomor meja and lama_main
                window.location.href = `/stop/${nomorMeja}?lama_main=${lamaMain}`;
            });
        });
    });
</script>
@endsection
