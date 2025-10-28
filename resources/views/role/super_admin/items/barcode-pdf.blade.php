<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Barcode - {{ $item->code }}</title>
<style>
    @page { size: 30mm 20mm; margin: 0; }
    html, body {
        margin: 0;
        padding: 0;
        width: 30mm;
        height: 20mm;
    }

    body {
        position: relative;
        font-family: Arial, sans-serif;
    }

    .label {
        width: 30mm;
        height: 20mm;
        position: relative;
        page-break-after: always;
    }

    .barcode {
        position: absolute;
        top: 3mm;   
        left: 50%;
        transform: translateX(-50%);
        width: 26mm;   
        height: auto;
        image-rendering: crisp-edges;
        filter: contrast(130%) brightness(110%);
    }

    .code {
        position: absolute;
        bottom: 7mm;
        left: 50%;
        transform: translateX(-50%);
        font-size: 6pt;
        font-weight: bold;
        margin: 0;
    }

    .name {
        position: absolute;
        bottom: 3mm;
        left: 50%;
        transform: translateX(-50%);
        font-size: 5.5pt;
        margin: 0;
    }
</style>
</head>
<body>
@for ($i = 0; $i < $jumlah; $i++)
    <div class="label">
        <img src="{{ $item->barcode_png_base64 }}" class="barcode" alt="barcode">
        <p class="code">{{ $item->code }}</p>
        <p class="name">{{ $item->name }}</p>
    </div>
@endfor
</body>
</html>