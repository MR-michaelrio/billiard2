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
                font-size: 20px; /* Readable font size */
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
            .details{
                margin-bottom: 10px;
            }
            .totals {
                margin-bottom: 3px;
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
            font-size: 20px;
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
        .details{
            margin-bottom: 10px;
        }
        .totals {
            margin-bottom: 3px;
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
    </style>
</head>
<body>
    <div class="receipt-content">
        <div class="header">
            Eighty Nine Pool
            <div>Date: {{ now()->format('d-m-Y') }}</div>
        </div>
        <!-- Info Section -->
            <div class="details">
                <span><b>Order ID:</b> {{$orderid->order_id}}</span>
                <span><b>Payment Due:</b> {{ now()->format('d-m-Y') }}</span>
            </div>

        <!-- Items Section -->
        <table>
            <tr>
                <th style="text-align:left;padding:0px 5px 0px 5px;">Qty</th>
                <th style="text-align:left;">Product</th>
                <th>Subtotal</th>
            </tr>
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
        //     window.print();
        // });

        // Redirect to index after printing
        window.addEventListener('afterprint', function() {
            const redirectUrl = '{{ route("bl.index") }}';
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        });

    </script>
</body>
</html>
