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

        /* ==== Kop Surat ==== */
        .kop-container {
            text-align: center;
            margin-bottom: 10px;
        }
        .kop-container img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
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

{{-- 🔹 Kop Surat --}}
@if($base64)
    <div class="kop-container">
        <img src="{{ $base64 }}" alt="Kop Surat">
    </div>
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

{{-- 🔹 Tabel Data --}}
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

{{-- 🔹 Footer --}}
<div class="footer">
    Dicetak pada: {{ now()->format('d-m-Y H:i') }} &nbsp;|&nbsp;
    Halaman <span class="page-number"></span>
</div>

</body>
</html>