<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receipt</title>
    <style>
        @media print {
            @page { size: 52mm auto; margin: 2mm; }
            body { margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px; }
            .receipt-content { width: 52mm; margin: 0 auto; text-align: left; }
            .header { text-align: center; font-weight: bold; margin-bottom: 10px; }
            .details { margin-bottom: 10px; }
            .totals { margin-bottom: 0px; }
            .details span, .totals span { display: block; }
            .totals .total-label { font-weight: bold; }
            .no-print { display: none; }
            table { page-break-inside: avoid; width: 100%; }
            tr { page-break-inside: avoid; }
        }
        body { font-family: Arial, sans-serif; font-size: 15px; line-height: 1; padding: 0; }
        .receipt-content { width: 52mm; margin: 0 auto; text-align: left; }
        .header { text-align: center; font-weight: bold; margin-bottom: 10px; }
        .details { margin-bottom: 10px; }
        .totals { margin-top: 0; }
        .details span, .totals span { display: block; }
        .totals .total-label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0px 0px; }
        th { text-align: left; }
        td { text-align: right; }
        #data-idtable { visibility: hidden; }
    </style>
</head>
<body>
    <div class="receipt-content">
        <div class="header">
            Play And Break Billiard
            <div>Date: {{ $tanggalmain }}</div>
        </div>

        <!-- Info Section -->
        <div class="details">
            <span><b>Order ID:</b> {{ $invoice->id_rental }}</span>
            <span><b>Account:</b> {{ $invoice->id_player }}</span>
            <span><b>Table:</b> {{ $no_meja }}</span>
            <span><b>Payment Due:</b> {{ $tanggalmain }}</span>
            <span><b>Player Name:</b> {{ $invoice->nonmember->nama ?? 'Non Member' }}</span>

            @foreach($rentals as $r)
                <span><b>Waktu Main:</b> {{ \Carbon\Carbon::parse($r->waktu_mulai)->format('H:i:s') }} - {{ \Carbon\Carbon::parse($r->waktu_akhir)->format('H:i:s') }}</span>
            @endforeach
        </div>

        <!-- Items Section -->
        <table>
            <tr>
                <th style="text-align:left;padding:0px 5px;">Qty</th>
                <th style="text-align:left;">Product</th>
                <th>Subtotal</th>
            </tr>

            <!-- Rental Items -->
            @foreach($rentals as $r)
                <tr>
                    <td style="text-align:left;padding:0px 5px;">1</td>
                    <td style="text-align:left;">Meja Billiard ({{ $r->lama_waktu }})</td>
                    <td>{{ number_format($r->harga_dihitung, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <!-- Food Items -->
            @foreach($makanan as $order)
                @foreach($order->items as $item)
                    <tr>
                        <td style="text-align:left;padding:0px 5px;">{{ $item->quantity }}</td>
                        <td style="text-align:left;">{{ $item->product_name }}</td>
                        <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach
        </table>

        <span id="data-idtable">{{ $invoice->id_belanja }}</span>

        <!-- Totals Section -->
        <div class="totals">
            <span class="total-label">Diskon: {{ number_format($diskon, 0, ',', '.') }}%</span>
            <span class="total-label">Grand Total:</span>
            <span>{{ number_format($total, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Print Button for Testing -->
    <div class="no-print">
        <button onclick="window.print()">Print</button>
    </div>

    <script>
        window.addEventListener('afterprint', function() {
            const idtable = document.getElementById('data-idtable').textContent;
            if(!idtable){
                window.location.href = '{{ route("bl.index") }}';
            }else{
                fetch('{{ route("print.status") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id_table: idtable })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '{{ route("bl.index") }}';
                    } else {
                        alert('Error updating status.');
                    }
                })
                .catch(error => console.error(error));
            }
        });
    </script>
</body>
</html>
