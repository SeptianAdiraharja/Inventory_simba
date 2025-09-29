<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Permintaan #{{ $cart->id }}</title>
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

    <!-- Isi konten -->
    <div class="content">
        <h2>Struk Permintaan Barang</h2>
        <h4>No. Permintaan: #{{ $cart->id }}</h4>
        <p>
            Pemesan: {{ $cart->user->name ?? ($cart->guest->name ?? 'Tidak Diketahui') }} <br>
            Email: {{ $cart->user->email ?? '-' }} <br>
            Tanggal Permintaan: {{ $cart->created_at->format('d-m-Y H:i') }}
        </p>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Kode</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cart->cartItems as $index => $cart_item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $cart_item->item->name }}</td>
                        <td>{{ $cart_item->item->code }}</td>
                        <td>{{ $cart_item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Total Barang:</strong> {{ $cart->cartItems->sum('quantity') }}</p>

        <div class="footer">
            <p>Dicetak pada {{ now()->format('d-m-Y H:i') }}</p>
        </div>
    </div>

</body>
</html>
