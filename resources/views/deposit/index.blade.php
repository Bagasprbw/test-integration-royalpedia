<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit - Royalpedia</title>
</head>

<body>
    <h1>Deposit</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('deposit.store') }}">
        @csrf
        <div>
            <label>Jumlah Deposit:</label>
            <input type="number" name="jumlah" min="10000" required>
        </div>
        <div>
            <label>Metode Pembayaran:</label>
            <select name="metode" required>
                <option value="BCA">BCA</option>
                <option value="Mandiri">Mandiri</option>
                <option value="BNI">BNI</option>
                <option value="BRI">BRI</option>
                <option value="GoPay">GoPay</option>
                <option value="OVO">OVO</option>
                <option value="DANA">DANA</option>
            </select>
        </div>
        <div>
            <label>Nomor Pembayaran:</label>
            <input type="text" name="no_pembayaran">
        </div>
        <button type="submit">Submit Deposit</button>
    </form>
</body>

</html>