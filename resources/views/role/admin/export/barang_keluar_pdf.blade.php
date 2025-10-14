<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang Keluar ({{ $period }})</title>
    <style>
        @page {
            margin: 80px 30px 40px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            position: relative;
        }

        /* Header gambar di atas */
        .kop-surat {
            position: fixed;
            top: -60px;
            left: 0;
            right: 0;
            text-align: center;
        }

        .kop-surat img {
            width: 100%;
            height: auto;
        }

        h2, h4 {
            margin: 0;
            padding: 0;
        }

        .content {
            margin-top: 150px; /* beri jarak supaya teks tidak menimpa gambar kop */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 20px;
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Gambar kop surat -->
    <div class="kop-surat">
        <img src="{{ public_path('assets/img/pdf/templatepdf.png') }}" alt="Kop Surat">
    </div>

    <div class="content">
        <h2>Laporan Barang Keluar ({{ $period }})</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Tanggal Keluar</th>
                    <th>Pengambil</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $itemOut)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $itemOut->item->name ?? '-' }}</td>
                        <td>{{ $itemOut->quantity }}</td>
                        <td>{{ $itemOut->released_date ?? $itemOut->created_at->format('d-m-Y') }}</td>
                        <td>{{ $itemOut->cart->user->name ?? 'Tamu/Non-User' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Dicetak pada {{ now()->format('d-m-Y H:i') }}</p>
        </div>
    </div>
</body>
</html>