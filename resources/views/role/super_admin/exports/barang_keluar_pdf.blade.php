<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Keluar</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h3 { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h3>📤 Laporan Barang Keluar
        ({{ ucfirst($period ?? "$startDate s/d $endDate") }})
    </h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Total</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $row->item->name }}</td>
                <td>{{ $row->quantity }}</td>
                <td>Rp {{ number_format($row->item->price,0,',','.') }}</td>
                <td>Rp {{ number_format($row->total_price,0,',','.') }}</td>
                <td>{{ $row->created_at->format('d-m-Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
