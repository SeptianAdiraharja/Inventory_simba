<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barcode - {{ $item->code }}</title>
    <style>
        @page {
            size: A4;
            margin: 5mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 7pt;
            margin: 0;
            padding: 0;
        }

        .sheet {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-items: flex-start;
            gap: 2mm;
        }

        .label {
            width: 33mm;
            height: 15mm;
            text-align: center;
            box-sizing: border-box;
            padding: 1mm;
            border: 0.1mm solid transparent; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .label img {
            width: 100%;
            height: auto;
            image-rendering: crisp-edges;
            image-rendering: pixelated;
            transform: scale(1.05); 
            filter: contrast(120%) brightness(105%);
        }

        .label p {
            font-size: 6pt;
            margin: 1mm 0 0 0;
            letter-spacing: 0.3pt;
        }
    </style>
</head>
<body>
    <div class="sheet">
        @for ($i = 0; $i < $jumlah; $i++)
            <div class="label">
                @if($item->barcode_png_base64)
                    <img 
                        src="data:image/png;base64,{{ base64_encode(base64_decode(str_replace('data:image/png;base64,', '', $item->barcode_png_base64))) }}" 
                        alt="barcode"
                    >
                    <p>{{ $item->code }}</p>
                    <p>{{ $item->name }}</p>
                @endif
            </div>
        @endfor
    </div>
</body>
</html>
