<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Keluar</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background: #f2f2f2; }
        .title { text-align: center; margin-top: 10px; }
        .footer { margin-top: 30px; font-size: 11px; text-align: right; }
    </style>
</head>
<body>

    @php
        $path = public_path('assets/img/pdf/templatepdf.png');
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    @endphp

    <div style="text-align:center; margin-bottom:20px;">
        <img src="{{ $base64 }}" style="width:100%; max-height:120px;" alt="Kop Surat">
    </div>

    <h2 class="title">LAPORAN BARANG KELUAR</h2>
    <p class="title">Periode: {{ $period ?? ($startDate.' s/d '.$endDate) }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Dikeluarkan Oleh</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
                <th>Tanggal Keluar</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($items as $i => $row)
                @php $grandTotal += $row->total_price; @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $row->item->name }}</td>
                    <td>{{ $row->user->name ?? '-' }}</td>
                    <td>{{ $row->quantity }}</td>
                    <td>Rp {{ number_format($row->item->price,0,',','.') }}</td>
                    <td>Rp {{ number_format($row->total_price,0,',','.') }}</td>
                    <td>{{ $row->created_at->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Grand Total</th>
                <th colspan="2">Rp {{ number_format($grandTotal,0,',','.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d-m-Y H:i') }}
    </div>

</body>
</html>
