@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <!-- Main content -->
        <div class="invoice p-3 mb-3">
            <!-- title row -->
            <div class="row">
                <div class="col-12">
                    <h4>
                        Billiard.
                        <small class="float-right">Date: {{ now()->format('d-m-Y') }}</small>
                    </h4>
                </div>
                <!-- /.col -->
            </div>
            <!-- info row -->
            @foreach($meja_rental2 as $r)
                <div class="row invoice-info">
                    <div class="col-sm-4 invoice-col">
                        <b>Order ID:</b> {{$r->id}}<br>
                        <b>Table:</b> {{$r->no_meja}}<br>
                        <b>Payment Due:</b> {{ now()->format('d-m-Y') }}<br>
                        <b>Account:</b> {{$r->id_player}}
                    </div>
                    <!-- /.col -->
                </div>
            @endforeach

            <!-- Table row -->
            <div class="row">
                <div class="col-12 table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Product</th>
                                <th>QTY</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($meja_rental2 as $r)
                            <tr>
                                <td>1</td>
                                <td>Meja Billiard</td>
                                <td><span id="lama_waktu">{{$lama_waktu}}</span></td>
                                <td>{{number_format($mejatotal)}}</td>
                            </tr>
                        @endforeach
                        @php 
                            $no = 2;
                        @endphp 
                        @foreach($makanan as $order)
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->price, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->

            <div class="row">
                <div class="col-6">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>Total:</th>
                                    <td>{{$total}}</td>
                                </tr>
                                <tr>
                                    <th>Diskon:</th>
                                    <td>
                                        <input type="text" name="diskon" id="diskon" value="0" >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->

            <!-- this row will not appear when printing -->
            <div class="row no-print">
                <div class="col-12">
                @foreach($meja_rental2 as $r)
                    <button type="button" class="submit-button btn btn-success float-right" data-metode="Cash" data-rental="{{ $r->id_rental }}" data-meja="{{ $r->no_meja }}">
                        Cash Payment {{ $r->no_meja }}
                    </button>
                    <button type="button" style="margin-right:10px" class="submit-button btn btn-success float-right" data-metode="Transfer" data-rental="{{ $r->id_rental }}" data-meja="{{ $r->no_meja }}">
                        Transfer Payment {{ $r->no_meja }}
                    </button>
                @endforeach
                </div>
            </div>
        </div>
        <!-- /.invoice -->
    </div>
</div>

<script>
document.querySelectorAll('.submit-button').forEach(button => {
    button.addEventListener('click', function() {
        const idRental = this.getAttribute('data-rental');
        const nomeja = this.getAttribute('data-meja');
        const metode = this.getAttribute('data-metode');
        const lamaWaktu = document.getElementById('lama_waktu').textContent;
        const diskon = document.getElementById('diskon').value;
        console.log("diskon", diskon);
        console.log('Sending request with idRental:', nomeja, 'lamaWaktu:', lamaWaktu);

        fetch('{{ route("bl.bayar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ no_meja: nomeja, lama_waktu: lamaWaktu, metode: metode, diskon: diskon })
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.json().then(error => { throw new Error(error.message || 'Unknown error'); });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log("reset stop watch: ",data.id_table)

                resetStopwatch(data.id_table);
                showAlert('Success','Order submitted successfully','success');
                // Redirect to print the receipt using id_rental
                const printUrl = `{{ route('print.receipt', ['id_rental' => ':id_rental']) }}`.replace(':id_rental', data.id_rental);
                window.location.href = printUrl;        
            } else {
                showAlert('Error','There was an error submitting the order','error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error','There was an error submitting the order. Please check the console for more details.','error');
        });
    });
});

function resetStopwatch(noMeja) {
    const stopwatchKey = `stopwatch_${noMeja}`;
    localStorage.removeItem(stopwatchKey);

    const element = document.querySelector(`.meja[data-nomor-meja="${noMeja}"]`);
    if (element) {
        const stopwatchElement = element.closest('.card-body').querySelector('.stopwatch');
        if (stopwatchElement) {
            stopwatchElement.innerHTML = '00:00:00';
        }
        element.classList.remove('meja-yellow', 'meja-red');
        element.classList.add('meja-green');
    }
}
</script>
@endsection
