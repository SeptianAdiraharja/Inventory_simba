<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Masuk</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
         /* ======================= */
        /*   KOP SURAT             */
        /* ======================= */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .kop-logo-col {
            width: 150px;
            vertical-align: top;
            border: none !important;
        }

        .kop-logo-img {
            width: 140px;
            height: auto;
            object-fit: contain;
            margin-left: 50px;
        }

        .kop-text-col {
            text-align: center;
            vertical-align: middle;
            border: none !important;
        }

        .kop-instansi {
            font-family: "Times New Roman", serif;
            font-size: 18pt;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .kop-dinas {
            font-family: "Times New Roman", serif;
            font-size: 18pt;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .kop-unit {
            font-family: "Times New Roman", serif;
            font-size: 22pt;
            font-weight: 900;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .kop-detail {
            font-family: "Times New Roman", serif;
            font-size: 13pt;
            margin-top: 8px;
            line-height: 1.35;
        }

        .kop-detail span {
            text-decoration: underline;
            color: #0070C0;
        }

        /* Garis Pembatas */
        .divider {
            border-top: 5px solid #000;
            width: 90%;
            margin: 5px auto 20px auto;
        }

         .table-container {
            width: 90%;
            margin: 0 auto;
        }


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

    {{-- ======================== --}}
    {{-- 🏢 KOP SURAT --}}
    {{-- ======================== --}}
    @if(isset($kopSurat))
    <table class="kop-table">
        <tr>
            <td class="kop-logo-col">
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
                    <img src="{{ $logoBase64 }}" class="kop-logo-img">
                @else
                    <img src="{{ public_path('images/default-logo.png') }}" class="kop-logo-img">
                @endif
            </td>

            <td class="kop-text-col">
                <div class="kop-instansi">{{ strtoupper($kopSurat->nama_instansi) }}</div>

                @if($kopSurat->nama_dinas ?? false)
                <div class="kop-dinas">{{ strtoupper($kopSurat->nama_dinas) }}</div>
                @endif

                <div class="kop-unit">{{ strtoupper($kopSurat->nama_unit) }}</div>

                <div class="kop-detail">
                    {{ $kopSurat->alamat }} <br>
                    Telepon: {{ $kopSurat->telepon }} <br>

                    Website: <span>{{ $kopSurat->website }}</span>
                    &nbsp;|&nbsp;
                    Email: <span>{{ $kopSurat->email }}</span>
                    <br>

                    {{ $kopSurat->kota }}
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>
    @endif

    <h2 class="title">LAPORAN BARANG MASUK</h2>
    <p class="title">Periode: {{ $period ?? ($startDate.' s/d '.$endDate) }}</p>

    <div class="table-container">
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
    </div>
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
