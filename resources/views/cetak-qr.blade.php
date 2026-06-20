<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code - {{ $batch->trace_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .print-container {
            background-color: #fff;
            width: 300px;
            margin: 0 auto;
            padding: 20px;
            border: 1px dashed #ccc;
            border-radius: 8px;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #047857; /* emerald-700 */
        }
        .subtitle {
            font-size: 12px;
            color: #6b7280; /* gray-500 */
            margin-bottom: 20px;
        }
        .qr-code {
            margin-bottom: 15px;
        }
        .trace-id {
            font-family: monospace;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .print-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #059669; /* emerald-600 */
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        @media print {
            body {
                background-color: #fff;
            }
            .print-container {
                border: none;
                margin: 0;
                padding: 0;
                width: 100%;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="print-container">
        <div class="title">GULA KELAPA ORGANIK</div>
        <div class="subtitle">Pindai kode untuk melacak asal-usul produk</div>
        
        <div class="qr-code">
            {!! QrCode::size(200)->generate(route('trace.public', $batch->trace_id)) !!}
        </div>
        
        <div class="trace-id">{{ $batch->trace_id }}</div>
    </div>

    <button class="print-btn" onclick="window.print()">Cetak Label</button>

</body>
</html>
