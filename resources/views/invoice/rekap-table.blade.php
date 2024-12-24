@extends('layout.main')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <!-- Token Input Form -->
            <div id="token-form" class="card-header">
                <h3 class="card-title">Enter Access Token</h3>
                <form id="tokenForm">
                    @csrf
                    <div class="form-group">
                        <input type="password" class="form-control" id="token" name="token" autocomplete="off" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>

            <!-- Rekap Table (Initially Hidden) -->
            <div class="card-body" id="rekap-table-container" style="display:none;">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Waktu Mulai</th>
                            <th>Waktu Akhir</th>
                            <th>Rental ID</th>
                            <th>No Meja</th>
                            <th>Lama Waktu</th>
                            <th>Harga Table</th>
                            <th>Harga Makanan</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $rekap)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($rekap['tanggal'])->format('d-m-Y H:i:s') }}</td>
                            <td>{{ \Carbon\Carbon::parse($rekap['tanggalakhir'])->format('d-m-Y H:i:s') }}</td>
                            <td>{{ $rekap['id_rental'] }}</td>
                            <td>{{ $rekap['no_meja'] }}</td>
                            <td>{{ $rekap['lama_waktu'] }}</td>
                            <td>{{ number_format($rekap['mejatotal']) }}</td>
                            <td>{{ number_format($rekap['total_makanan']) }}</td>
                            <td>{{ number_format($rekap['total']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Waktu Mulai</th>
                            <th>Waktu Akhir</th>
                            <th>Rental ID</th>
                            <th>No Meja</th>
                            <th>Lama Waktu</th>
                            <th>Harga Table</th>
                            <th>Harga Makanan</th>
                            <th>Total Harga</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Loading Spinner (Initially Hidden) -->
            <div id="loading" style="display: none; text-align: center; padding: 20px;">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Loading...</p>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('tokenForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent form from submitting traditionally

        const token = document.getElementById('token').value;
        const validToken = "892406"; // Define the valid token

        // Show the loading spinner
        document.getElementById('loading').style.display = 'block';

        // Simulate processing
        setTimeout(function() {
            // Check if the token is correct
            if (token === validToken) {
                // Hide the token form and loading spinner, show the table
                document.getElementById('token-form').style.display = 'none';
                document.getElementById('loading').style.display = 'none';
                document.getElementById('rekap-table-container').style.display = 'block';
            } else {
                // Hide the loading spinner and show an alert for invalid token
                document.getElementById('loading').style.display = 'none';
                showAlert('Error','Invalid Token! Please try again.','error');
            }
        }, 1000); // Simulating a 1-second delay for processing
    });
</script>

@endsection
