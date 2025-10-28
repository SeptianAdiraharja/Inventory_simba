<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Keluar</title>
    <style>
        @page {
            size: A4 landscape; /* ✅ Orientasi halaman jadi LANDSCAPE */
            margin: 50px 30px 80px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
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
            font-weight: bold;
        }

        .title {
            text-align: center;
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
        }

        .subtitle {
            text-align: center;
            margin-top: 5px;
            font-size: 13px;
        }

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

        .kop-container {
            text-align: center;
            margin-bottom: 15px;
        }

        .kop-container img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
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

    {{-- 🔹 Kop Surat --}}
    @if($base64)
        <div class="kop-container">
            <img src="{{ $base64 }}" alt="Kop Surat">
        </div>
    @endif

    {{-- 🔹 Judul --}}
    <h2 class="title">LAPORAN BARANG KELUAR</h2>
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

    {{-- 🔹 Tabel Data --}}
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Penerima</th>
                <th>Tanggal Keluar</th>
                <th>Jumlah</th>
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

                // 🔹 Tentukan penerima
                $penerima = '-';
                if (!empty($row->cart?->user?->name)) {
                    $penerima = $row->cart->user->name;
                } elseif (!empty($row->guestCart?->guest?->name)) {
                    $penerima = $row->guestCart->guest->name;
                } elseif (!empty($row->user?->name)) {
                    $penerima = $row->user->name;
                } elseif (!empty($row->guest?->name)) {
                    $penerima = $row->guest->name;
                }
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row->item->name ?? '-' }}</td>
                <td>{{ $penerima }}</td>
                <td>{{ optional($row->created_at)->format('d-m-Y') ?? '-' }}</td>
                <td>{{ number_format($jumlah, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Tidak ada data barang keluar pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>

    {{-- 🔹 Total --}}
    <tfoot>
        <tr>
            <th colspan="4" style="text-align:right;">Total Jumlah Barang</th>
            <th>{{ number_format($totalQuantity, 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>


    {{-- 🔹 Footer --}}
    <div class="footer">
        Dicetak pada: {{ now()->format('d-m-Y H:i') }} &nbsp;|&nbsp;
        Halaman <span class="page-number"></span>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                if ($PAGE_COUNT > 0) {
                    $font = $fontMetrics->get_font("DejaVu Sans, Helvetica, sans-serif", "normal");
                    $size = 10;
                    $pageText = "Halaman " . $PAGE_NUM . " dari " . $PAGE_COUNT;

                    // 🔹 Posisi tengah bawah kertas A4 landscape
                    $width = $pdf->get_width();
                    $textWidth = $fontMetrics->get_text_width($pageText, $font, $size);
                    $x = ($width - $textWidth) / 2;  // hitung posisi tengah
                    $y = $pdf->get_height() - 30;    // jarak 30px dari bawah

                    $pdf->text($x, $y, $pageText, $font, $size);
                }
            ');
        }
    </script>
</body>
</html>
