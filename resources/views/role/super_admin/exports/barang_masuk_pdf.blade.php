<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Masuk</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background: #f2f2f2; }
        .title { text-align: center; margin-top: 10px; }
        .footer { margin-top: 30px; font-size: 11px; text-align: right; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>

    @php
        $path = public_path('assets/img/pdf/kopsurat.png');
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        $grandTotal = 0;
        $totalQuantity = 0;
    @endphp

    <!-- 🧢 Kop Surat -->
    <header>
        <img src="{{ $base64 }}" alt="Kop Surat">
    </header>

    <!-- 🏷️ Judul -->
    <h2 class="title">LAPORAN BARANG MASUK</h2>
    <p class="title">Periode: {{ $period ?? ($startDate.' s/d '.$endDate) }}</p>

    <!-- 📊 Tabel Data -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Supplier</th>
                <th>Tanggal Masuk</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $row)
                @php 
                    $grandTotal += $row->total_price;
                    $totalQuantity += $row->quantity;
                @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $row->item->name }}</td>
                    <td>{{ $row->supplier->name ?? '-' }}</td>
                    <td>{{ $row->created_at->format('d-m-Y') }}</td>
                    <td>{{ $row->quantity }}</td>
                    <td>{{ $row->item->unit->name ?? '-' }}</td>
                    <td>Rp {{ number_format($row->item->price,0,',','.') }}</td>
                    <td>Rp {{ number_format($row->total_price,0,',','.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align:right;">Total Jumlah</th>
                <th colspan="4">{{ number_format($totalQuantity,0,',','.') }}</th>
            </tr>
            <tr>
                <th colspan="4" style="text-align:right;">Grand Total Harga</th>
                <th colspan="4">Rp {{ number_format($grandTotal,0,',','.') }}</th>
            </tr>
        </tfoot>
    </table>
    <div class="footer">
        Dicetak pada: {{ now()->format('d-m-Y H:i') }}<br>
        Halaman <span class="page-number"></span>
    </div>
</body>
</html>
