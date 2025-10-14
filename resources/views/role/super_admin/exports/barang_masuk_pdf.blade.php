<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Masuk</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 100px 30px 80px 30px;
        }
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        header {
            position: fixed;
            top: -90px;
            left: 0;
            right: 0;
            text-align: center;
        }
        header img {
            width: 100%;
            height: auto;
            object-fit: cover;
            max-height: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        th {
            background: #f2f2f2;
        }
        .title {
            text-align: center;
            margin-top: 10px;
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    @php
        $path = public_path('assets/img/pdf/kopsurat.png');
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
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
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
                <th>Tanggal Masuk</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($items as $i => $row)
                @php $grandTotal += $row->total_price; @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $row->item->name }}</td>
                    <td>{{ $row->supplier->name ?? '-' }}</td>
                    <td>{{ $row->quantity }}</td>
                    <td>Rp {{ number_format($row->item->price,0,',','.') }}</td>
                    <td>Rp {{ number_format($row->total_price,0,',','.') }}</td>
                    <td>{{ $row->created_at->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Grand Total</th>
                <th colspan="2">Rp {{ number_format($grandTotal,0,',','.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <!-- 🕒 Footer -->
    <footer>
        Dicetak pada: {{ now()->format('d-m-Y H:i') }}<br>
        Halaman <span class="page-number"></span>
    </footer>

    <!-- 📄 Script Nomor Halaman -->
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("Helvetica", "normal");
                $size = 10;
                $text = "Halaman " . $PAGE_NUM . " dari " . $PAGE_COUNT;
                $pdf->text(270, 820, $text, $font, $size);
            ');
        }
    </script>

</body>
</html>
