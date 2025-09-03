@extends('layout.main')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="invoice p-3 mb-3">
            <div class="row">
                <div class="col-12">
                    <h4>
                        Billiard.
                        <small class="float-right">Date: {{ now()->format('d-m-Y') }}</small>
                    </h4>
                </div>
            </div>

            <!-- Info row per rental -->
            @foreach($meja_rental as $r)
                <div class="row invoice-info">
                    <div class="col-sm-4 invoice-col">
                        <b>Order ID:</b> {{$r->id}}<br>
                        <b>Table:</b> {{$r->no_meja}}<br>
                        <b>Payment Due:</b> {{ now()->format('d-m-Y') }}<br>
                        <b>Account:</b> {{$r->id_player ?? '-'}}
                    </div>
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
                                <th>QTY / Lama Waktu</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($meja_rental as $r)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td >Meja Billiard</td>
                                    <td>
                                        <span id="lama_waktu_{{ $r->id }}" >{{ $r->lama_waktu ?? $r->lama_waktu_hitung ?? '00:00:00' }}</span>
                                    </td>
                                    <td>{{ number_format($r->harga_per_rental, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach

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
            </div>

            <!-- Total & Diskon -->
            <div class="row">
                <div class="col-6">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>Total:</th>
                                    <td>{{ $total }}</td>
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
            </div>

            <!-- Payment buttons -->
            <div class="row no-print">
                <div class="col-12">
                    @foreach($meja_rental->groupBy('no_meja') as $no_meja => $rentals)
                        @php
                            $firstRental = $rentals->first();
                        @endphp
                        <button type="button" class="submit-button btn btn-success float-right ml-2" 
                                data-metode="Cash" 
                                data-meja="{{ $no_meja }}" data-rental="{{ $firstRental->id }}">
                            Cash Payment {{ $no_meja }}
                        </button>
                        <button type="button" class="submit-button btn btn-success float-right ml-2" 
                                data-metode="Transfer" 
                                data-meja="{{ $no_meja }}" data-rental="{{ $firstRental->id }}">
                            Transfer Payment {{ $no_meja }}
                        </button>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.querySelectorAll('.submit-button').forEach(button => {
    button.addEventListener('click', function() {
        const idRental = this.getAttribute('data-rental');
        const noMeja = this.getAttribute('data-meja');
        const metode = this.getAttribute('data-metode');
        const lamaWaktuEl = document.getElementById(`lama_waktu_${idRental}`);
        const lamaWaktu = lamaWaktuEl ? lamaWaktuEl.textContent : '00:00:00';
        const diskon = document.getElementById('diskon').value;

        fetch('{{ route("bl.bayar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ no_meja: noMeja, lama_waktu: lamaWaktu, metode: metode, diskon: diskon, idrental:idRental })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.success) {
                resetStopwatch(noMeja);
                showAlert('Success','Order submitted successfully','success');

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
    const element = document.querySelector(`.meja[data-nomor-meja="${noMeja}"]`);
    if (element) {
        const stopwatchElement = element.closest('.card-body')?.querySelector('.stopwatch');
        if (stopwatchElement) stopwatchElement.innerHTML = '00:00:00';
        element.classList.remove('meja-yellow','meja-red');
        element.classList.add('meja-green');
    }
}
</script>
@endsection
