<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Keluar</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 12px; 
            margin: 0;
            padding: 0;
            position: relative;
            min-height: 100vh;
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
            margin-bottom: 5px;
        }
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 11px;
            line-height: 1.4;
            padding: 8px 0;
        }
        .page-number:after {
            content: counter(page);
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            color: #fff;
        }
        .bg-primary { background-color: #0d6efd; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-secondary { background-color: #6c757d; }
    </style>
</head>
<body>

    @php
        $path = public_path('assets/img/pdf/templatepdf.png');
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $grandTotal = $items->sum(fn($row) => ($row->item->price ?? 0) * ($row->quantity ?? 0));
    @endphp

    <!-- Kop Surat -->
    <div style="text-align:center; margin-bottom:20px;">
        <img src="{{ $base64 }}" style="width:100%; max-height:120px;" alt="Kop Surat">
    </div>

    <!-- Judul -->
    <h2 class="title">LAPORAN BARANG KELUAR</h2>
    <p class="title">Periode: {{ $period ?? ($startDate.' s/d '.$endDate) }}</p>

    <!-- Tabel Data -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Total</th>
                <th>Tanggal</th>
                <th>Dikeluarkan Oleh</th>
                <th>Tipe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row->item->name ?? '-' }}</td>
                <td>{{ $row->quantity ?? 0 }}</td>
                <td>Rp {{ number_format($row->item->price ?? 0, 0, ',', '.') }}</td>
                <td>Rp {{ number_format(($row->item->price ?? 0) * ($row->quantity ?? 0), 0, ',', '.') }}</td>
                <td>{{ $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-' }}</td>
                <td>{{ $row->user->name ?? $row->guest->name ?? '-' }}</td>
                <td>
                    @if(isset($row->user))
                        <span class="badge bg-primary">User</span>
                    @elseif(isset($row->guest))
                        <span class="badge bg-warning">Guest</span>
                    @else
                        <span class="badge bg-secondary">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="font-style:italic;">Tidak ada data barang keluar</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Grand Total</th>
                <th colspan="4">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        Dicetak pada: {{ now()->format('d-m-Y H:i') }}<br>
        Halaman <span class="page-number"></span>
    </div>

</body>
</html>
