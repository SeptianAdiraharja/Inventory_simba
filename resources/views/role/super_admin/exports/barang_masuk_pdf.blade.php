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
        @page {
            margin-top: 80px;
            margin-bottom: 40px;
        }
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
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

    {{-- 🔹 KOP SURAT --}}
    @if(isset($kopSurat))
        <table style="width:100%; border:none; border-collapse:collapse; margin-bottom:10px;">
            <tr>
            {{-- Logo kiri --}}
                @php
                    $logoPath = public_path('storage/' . $kopSurat->logo);
                    $logoBase64 = '';
                    if (file_exists($logoPath)) {
                        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                        $data = file_get_contents($logoPath);
                        $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                @endphp

                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width:145px; height:auto; object-fit:contain; display:block; margin:0;">
                @else
                    <img src="{{ public_path('images/default-logo.png') }}" style="width:145px; height:auto; object-fit:contain; display:block; margin:0;">
                @endif
                <td style="text-align:center; vertical-align:middle; border:none;">
                    <div style="font-family:'Times New Roman', serif; margin:0; padding:0; line-height:1.4;">
                        <div style="font-size:17pt; font-weight:700; text-transform:uppercase; margin:0;">
                            {{ strtoupper($kopSurat->nama_instansi) }}
                        </div>
                        <div style="font-size:20pt; font-weight:900; text-transform:uppercase; margin-top:3px;">
                            {{ strtoupper($kopSurat->nama_unit) }}
                        </div>
                        <div style="font-size:11.5pt; font-weight:500; margin-top:6px;">
                            {{ $kopSurat->alamat }}
                            Telp: {{ $kopSurat->telepon }} <br>
                            Website: {{ $kopSurat->website }} | Email: {{ $kopSurat->email }}<br>
                            {{ $kopSurat->kota }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        {{-- Garis bawah --}}
        <div style="border-top:3px solid #000; margin-top:5px; margin-bottom:20px;"></div>
    @endif

    <h2 class="title">LAPORAN BARANG MASUK</h2>
    <p class="title">Periode: {{ $period ?? ($startDate.' s/d '.$endDate) }}</p>

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
    <script type="text/php">
    if (isset($pdf)) {
        $pdf->page_script('
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $size = 9;
            $date = date("d-m-Y H:i");
            $pageText = "Dicetak pada: " . $date . " | Halaman " . $PAGE_NUM . " dari " . $PAGE_COUNT;
            $width = $fontMetrics->get_text_width($pageText, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 25;
            $pdf->text($x, $y, $pageText, $font, $size);
        ');
    }
    </script>
</body>
</html>
