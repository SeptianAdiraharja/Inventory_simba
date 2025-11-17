<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Reject</title>
    <style>
        /* ==== Layout dan Font ==== */
        @page { margin: 50px 30px 80px 30px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
        }

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

        /* ==== Judul dan Subjudul ==== */
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0 4px;
            text-transform: uppercase;
        }
        .subtitle {
            text-align: center;
            font-size: 13px;
            margin: 0 0 10px;
        }

        /* ==== Tabel Data ==== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tfoot th {
            background-color: #fafafa;
            font-weight: bold;
        }

        /* ==== Footer ==== */
        .footer {
            position: fixed;
            bottom: 15px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11px;
            color: #333;
        }
        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>

@php
    use Carbon\Carbon;

    $path = public_path('assets/img/pdf/kopsurat.png');
    $grandTotal = 0;
    $totalQuantity = 0;

    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    } else {
        $base64 = null;
    }
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

{{-- 🔹 Judul --}}
<h2 class="title">LAPORAN REJECT BARANG</h2>
<p class="subtitle">
    Periode:
    @if(!empty($period))
        {{ $period }}
    @elseif(!empty($startDate) && !empty($endDate))
        {{ Carbon::parse($startDate)->format('d M Y') }} s/d {{ Carbon::parse($endDate)->format('d M Y') }}
    @else
        Semua Periode
    @endif
</p>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Status</th>
                <th>Tanggal Reject</th>
                <th>Jumlah</th>
                <th>Harga Satuan (Rp)</th>
                <th>Total Harga (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $row)
                @php
                    $jumlah   = $row->quantity ?? 0;
                    $harga    = $row->item->price ?? 0;
                    $subtotal = $row->total_price ?? ($jumlah * $harga);
                    $grandTotal += $subtotal;
                    $totalQuantity += $jumlah;
                    $role = $row->role ?? 'Reject';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->item->name ?? '-' }}</td>
                    <td>{{ $role }}</td>
                    <td>{{ optional($row->created_at)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ number_format($jumlah, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($harga, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Tidak ada data reject pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>

        {{-- 🔹 Total --}}
        <tfoot>
            <tr>
                <th colspan="4" style="text-align:right;">Total Jumlah Barang</th>
                <th colspan="3">{{ number_format($totalQuantity, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="4" style="text-align:right;">Grand Total Harga</th>
                <th colspan="3">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</div>

{{-- 🔹 Footer --}}
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
