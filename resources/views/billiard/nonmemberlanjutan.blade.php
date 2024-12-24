@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <h1>Meja {{$no_meja}}</h1>
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Non Member</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{route('bl.storenonmember')}}" method="post">
                @csrf
                <input type="hidden" value="{{$no_meja}}" name="no_meja" class="form-control">
                <div class="card-body">
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" class="form-control" id="nama" name='nama' placeholder="Nama">
                        <div id="nama-list" class="list-group"></div>
                    </div>
                    <div class="form-group">
                        <label for="No_Telp">Nomor Telp</label>
                        <input type="text" class="form-control" id="No_Telp" name='no_telp' placeholder="08.......">
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
<!-- Tambahkan JQuery dan AJAX Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Fungsi autocomplete saat pengguna mengetik di input 'nama'
    $('#nama').on('keyup', function() {
        var query = $(this).val(); // Ambil nilai dari input

        if (query.length > 1) { // Mulai pencarian jika panjang input lebih dari 1 karakter
            $.ajax({
                url: "{{ route('search.names') }}", // URL yang digunakan untuk pencarian
                type: "GET",
                data: { term: query }, // Kirim query ke server
                success: function(data) {
                    // Kosongkan list sebelum menambah data baru
                    $('#nama-list').empty();
                    
                    // Iterasi melalui hasil dan tampilkan di dropdown
                    $.each(data, function(index, item) {
                        $('#nama-list').append('<a href="#" class="list-group-item list-group-item-action" data-id="'+ item.id +'" data-telp="'+ item.no_telp +'">' + item.nama + '</a>');
                    });

                    // Tampilkan dropdown
                    $('#nama-list').show();
                }
            });
        } else {
            $('#nama-list').empty().hide(); // Sembunyikan list jika input terlalu pendek
        }
    });

    // Saat pengguna mengklik salah satu hasil, isi nilai input dengan data yang dipilih
    $('#nama-list').on('click', 'a', function(e) {
        e.preventDefault();

        var nama = $(this).text(); // Ambil teks nama
        var no_telp = $(this).data('telp'); // Ambil nomor telepon dari data-telp

        // Isi input dengan nilai yang dipilih
        $('#nama').val(nama);
        $('#No_Telp').val(no_telp);

        // Kosongkan dan sembunyikan list
        $('#nama-list').empty().hide();
    });

    // Sembunyikan list jika klik di luar area
    $(document).on('click', function(event) {
        if (!$(event.target).closest('#nama').length && !$(event.target).closest('#nama-list').length) {
            $('#nama-list').empty().hide();
        }
    });
});
</script>
@endsection
