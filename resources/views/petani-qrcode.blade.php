<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Petani - {{ $petani->nama }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 380px;
            width: 100%;
        }
        .logo-area {
            background: linear-gradient(135deg, #1a7c4b, #2da862);
            color: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .logo-area h1 { font-size: 18px; font-weight: 700; }
        .logo-area p { font-size: 12px; opacity: 0.8; margin-top: 4px; }
        .qr-wrapper {
            background: #f8fafc;
            border: 3px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            display: inline-block;
        }
        .petani-info { margin-bottom: 20px; }
        .kode-badge {
            display: inline-block;
            background: #1a7c4b;
            color: white;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 3px;
            padding: 10px 24px;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .petani-nama {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .petani-meta {
            font-size: 13px;
            color: #64748b;
        }
        .stats-row {
            display: flex;
            gap: 12px;
            margin: 16px 0;
            justify-content: center;
        }
        .stat-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 10px 16px;
            flex: 1;
        }
        .stat-box .num { font-size: 22px; font-weight: 700; color: #1a7c4b; }
        .stat-box .lbl { font-size: 11px; color: #64748b; }
        .scan-hint {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            border-radius: 8px;
            padding: 10px;
            font-size: 12px;
            margin-bottom: 20px;
        }
        .print-btn, .back-btn {
            display: inline-block;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            margin: 4px;
        }
        .print-btn { background: #1a7c4b; color: white; }
        .back-btn { background: #e2e8f0; color: #475569; }
        @media print {
            body { background: white; }
            .print-btn, .back-btn { display: none; }
            .card { box-shadow: none; }
        }
        .footer-note { font-size: 11px; color: #94a3b8; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-area">
            <h1>🌿 e-Traceability Gula Kelapa</h1>
            <p>Sistem Ketelusuran Rantai Pasok</p>
        </div>

        <div class="petani-info">
            <div class="kode-badge">{{ $petani->kode_petani }}</div>
            <div class="petani-nama">{{ $petani->nama }}</div>
            <div class="petani-meta">
                {{ $petani->desa ? $petani->desa . ', ' : '' }}{{ $petani->kecamatan ?? '' }}<br>
                {{ $petani->no_hp ?? '-' }}
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-box">
                <div class="num">{{ $petani->lahans->count() }}</div>
                <div class="lbl">Lahan</div>
            </div>
            <div class="stat-box">
                <div class="num">{{ $petani->lahans->sum('jumlah_pohon') }}</div>
                <div class="lbl">Total Pohon</div>
            </div>
        </div>

        <div class="qr-wrapper">
            {!! $qrCode !!}
        </div>

        <div class="scan-hint">
            📱 Scan QR code ini saat menyetorkan produk gula ke sistem
        </div>

        <div>
            <button class="print-btn" onclick="window.print()">🖨️ Cetak QR Code</button>
            <a class="back-btn" href="{{ url()->previous() }}">← Kembali</a>
        </div>

        <div class="footer-note">Dicetak {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</body>
</html>
