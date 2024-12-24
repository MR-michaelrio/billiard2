<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receipt</title>
    <style>
        /* Print-specific styles */
        @media print {
            @page {
                size: 58mm auto; /* Fixed width, dynamic height */
                margin: 5mm; /* Small margin */
            }
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                font-size: 12px; /* Readable font size */
            }
            .receipt-content {
                width: 45mm; /* Width of receipt paper */
                margin: 0 auto;
                text-align: left;
            }
            .header {
                text-align: center;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .details, .totals {
                margin-bottom: 10px;
            }
            .details span, .totals span {
                display: block;
            }
            .totals .total-label {
                font-weight: bold;
            }
            .no-print {
                display: none;
            }
            /* This will ensure the header is not repeated on the next page */
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid; /* Prevent table row from breaking */
                page-break-after: auto;
            }
        }

        /* Optional styles for screen */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1;
            padding: 10px;
        }
        .receipt-content {
            width: 45mm;
            margin: 0 auto;
            text-align: left;
        }
        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .details, .totals {
            margin-bottom: 10px;
        }
        .totals {
            margin-top: 10px;
        }
        .details span, .totals span {
            display: block;
        }
        .totals .total-label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 5px 0;
        }
        th {
            text-align: left;
        }
        td {
            text-align: right;
        }
        #data-idtable{
            visibility: hidden;
        }
    </style>
</head>
<body>
    <div class="receipt-content">
        <div class="header">
            Eighty Nine Pool
            <div>Date: {{ $tanggalmain }}</div>
        </div>
        <!-- Info Section -->
        <!-- @foreach($meja_rental2 as $r) -->
            <div class="details">
                <span><b>Order ID:</b> {{$meja_rental->id_rental}}</span>
                <span><b>Account:</b> {{$meja_rental->invoices->id_player}}</span>
                <span><b>Table:</b> {{$meja_rental->no_meja}}</span>
                <span><b>Payment Due:</b> {{ $tanggalmain }}</span>
                <span><b>Player Name:</b> {{$meja_rental->invoices->nonmember->nama}}</span>
                @foreach($meja_rental2 as $r)
                    <span><b>Waktu Main:</b> {{\Carbon\Carbon::parse($r->waktu_mulai)->format('H:i:s')}} - {{\Carbon\Carbon::parse($r->waktu_akhir)->format('H:i:s')}}</span>
                @endforeach
            </div>
        <!-- @endforeach -->

        <!-- Items Section -->
        <table>
            <tr>
                <th style="text-align:left;padding:0px 5px 0px 5px;">Qty</th>
                <th style="text-align:left;">Product</th>
                <th>Subtotal</th>
            </tr>
                @foreach($meja_rental2 as $r)
                    <tr>
                        <td style="text-align:left;padding:0px 5px 0px 5px;">1</td>
                        <td style="text-align:left;">Meja Billiard ({{$lama_waktu}})</td>
                        <td>{{ number_format($mejatotal) }}</td>
                    </tr>
                @endforeach
                @php 
                    $no = 2;
                @endphp 
                @foreach($makanan as $order)
                    @foreach($order->items as $item)
                        <tr>
                            <td style="text-align:left;padding:0px 5px 0px 5px;">{{ $item->quantity }}</td>
                            <td style="text-align:left;">{{ $item->product_name }}</td>
                            <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endforeach
                <span id="data-idtable">{{$invoice->id_belanja}}</span>
        </table>

        <!-- Totals Section -->
        <div class="totals">
            <span class="total-label">Total:</span>
            <span>{{ number_format($total, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Optional Print Button for Testing -->
    <div class="no-print">
        <button onclick="window.print()">Print</button>
    </div>

    <script>
        // Automatically trigger printing when the page loads
        // window.addEventListener('load', function() {
        //     window.print(); // Print the receipt twice
        // });

        window.addEventListener('afterprint', function() {
            const idtable = document.getElementById('data-idtable').textContent;
            console.log('ID Table:', idtable); // For debugging

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
                console.log('Response data:', data); // For debugging
                if (data.success) {
                    // Perform redirection if success
                    const redirectUrl = '{{ route("bl.index") }}';
                    window.location.href = redirectUrl;
                } else {
                    showAlert('Error','There was an error updating the status','error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error','There was an error during the status update. Please check the console for more details.','error');
            });
        });

    </script>
</body>
</html>
