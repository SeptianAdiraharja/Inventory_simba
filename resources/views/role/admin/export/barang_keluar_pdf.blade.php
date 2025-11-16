<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Keluar</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <style>
        @page {
            size: A4 landscape;
            margin: 40px 30px 70px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
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

        /* ======================= */
        /*   TABLE LAPORAN         */
        /* ======================= */
        .data-table {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        .data-table th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .total-row td {
            background: #fafafa;
            font-weight: bold;
        }

        /* ======================= */
        /*   TITLE & PERIOD        */
        /* ======================= */
        .report-title {
            text-align: center;
            margin: 0;
        }

        .report-period {
            text-align: center;
            margin: 3px 0 10px 0;
        }

        /* ======================= */
        /*   FOOTER                */
        /* ======================= */
        .footer {
            position: fixed;
            bottom: 15px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #444;
        }
    </style>

</head>
<body>

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

    {{-- ======================== --}}
    {{-- TITLE --}}
    {{-- ======================== --}}
    <h2 class="report-title">LAPORAN BARANG KELUAR</h2>

    <p class="report-period">
        Periode:
        {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }}
        -
        {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
    </p>

    {{-- ======================== --}}
    {{-- TABEL DATA --}}
    {{-- ======================== --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th width="32%">NAMA BARANG</th>
                <th width="22%">PENERIMA</th>
                <th width="10%">ROLE</th>
                <th width="15%">TANGGAL</th>
                <th width="10%">JUMLAH</th>
            </tr>
        </thead>

        <tbody>
            @php $grandTotal = 0; @endphp

            @foreach($items as $i => $item)
                @php
                    $grandTotal += $item->quantity;

                    // Gunakan logika yang sama seperti di Excel
                    $namaBarang = $item->item->name ?? 'Barang Dihapus';

                    $penerima = $item->pengambil ?? (
                        isset($item->type)
                        ? ($item->type === 'pegawai'
                            ? ($item->cart->user->name ?? 'Tamu/Non-User')
                            : ($item->guestCart->guest->name ?? 'Tamu'))
                        : ($item->cart->user->name ??
                        $item->guestCart->guest->name ??
                        'Tamu/Non-User')
                    );

                    $jenis = $item->type ?? (isset($item->cart) ? 'pegawai' : 'tamu');
                    $role = $jenis === 'pegawai' ? 'Pegawai' : 'Tamu';

                    $satuan = $item->item->unit->name ?? 'pcs';
                @endphp

                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="text-left">{{ $namaBarang }}</td>
                    <td class="text-left">{{ $penerima }}</td>
                    <td>{{ $role }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->released_at ?? $item->created_at)->format('d-m-Y') }}</td>
                    <td>{{ $item->quantity }} {{ $satuan }}</td>
                </tr>
            @endforeach

            @if($items->count() > 0)
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL JUMLAH BARANG</td>
                <td>{{ $grandTotal }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- ======================== --}}
    {{-- FOOTER --}}
    {{-- ======================== --}}
    <div class="footer">
        Dicetak pada: {{ now()->format('d-m-Y H:i') }} —
        Halaman <span class="page-number"></span>
    </div>

    <script type="text/php">
    if (isset($pdf)) {
        $pdf->page_script('
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 9;
            $pageText = "Halaman " . $PAGE_NUM . " dari " . $PAGE_COUNT;

            $width = $pdf->get_width();
            $textWidth = $fontMetrics->get_text_width($pageText, $font, $size);
            $x = ($width - $textWidth) / 2;
            $y = $pdf->get_height() - 30;

            $pdf->text($x, $y, $pageText, $font, $size);
        ');
    }
    </script>

</body>
</html>
