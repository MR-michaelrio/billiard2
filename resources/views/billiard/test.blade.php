<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receipt</title>
</head>
<body>
    <form action="{{ route('print.receipt') }}" method="post">
        @csrf
        <div>
            <label for="receipt_text">Receipt Text:</label><br>
            <textarea id="receipt_text" name="receipt_text" rows="10" cols="50">
Hello World!
This is a thermal receipt.
------------------------
Item 1         10,000
Item 2         20,000
------------------------
Total          30,000
            </textarea>
        </div>
        <br>
        <button type="submit">Print Receipt</button>
    </form>

    @if (session('success'))
        <div style="color: green;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div style="color: red;">
            {{ session('error') }}
        </div>
    @endif
</body>
</html>
