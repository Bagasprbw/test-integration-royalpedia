<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit History - Royalpedia</title>
</head>

<body>
    <h1>Deposit History</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Status</th>
                <th>No Pembayaran</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deposits as $deposit)
                <tr>
                    <td>{{ $deposit->id }}</td>
                    <td>Rp {{ number_format($deposit->jumlah, 0, ',', '.') }}</td>
                    <td>{{ $deposit->metode }}</td>
                    <td>{{ $deposit->status }}</td>
                    <td>{{ $deposit->no_pembayaran }}</td>
                    <td>{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>