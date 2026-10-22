<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif">
    <div style="max-width:600px;margin:0 auto;padding:32px 16px">
        <div style="text-align:center;margin-bottom:24px">
            <h1 style="font-size:24px;color:#111827;margin:0">⭐ BonusHub</h1>
        </div>

        <div style="background:white;border-radius:16px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.1)">
            <div style="text-align:center;margin-bottom:24px">
                <span style="font-size:48px">⏳</span>
            </div>

            <h2 style="font-size:20px;color:#111827;text-align:center;margin:0 0 8px">Mata anda akan tamat tempoh</h2>
            <p style="font-size:15px;color:#6b7280;text-align:center;margin:0 0 24px">
                Hai {{ $customerName }}, jangan biarkan mata anda hilang.
            </p>

            <div style="background:#fffbeb;border:2px solid #fde68a;border-radius:12px;padding:16px;text-align:center;margin-bottom:24px">
                <div style="font-size:14px;color:#b45309;margin-bottom:4px">Mata yang akan tamat</div>
                <div style="font-size:28px;font-weight:700;color:#92400e">{{ number_format($points) }} mata</div>
                <div style="font-size:13px;color:#92400e;margin-top:8px">pada <strong>{{ $expiresOn }}</strong></div>
            </div>

            <div style="background:#f9fafb;border-radius:8px;padding:16px;margin-bottom:24px">
                <table style="width:100%;border-collapse:collapse;font-size:14px">
                    <tr>
                        <td style="padding:6px 0;color:#6b7280">Merchant</td>
                        <td style="padding:6px 0;color:#111827;font-weight:500;text-align:right">{{ $merchantName }}</td>
                    </tr>
                </table>
            </div>

            <p style="font-size:14px;color:#6b7280;margin:0 0 20px">
                Mata tamat jika tiada aktiviti. Buat satu pembelian atau tebus ganjaran sebelum tarikh di atas untuk mengekalkan mata anda. Lihat <a href="{{ url('/terms/points') }}" style="color:#6366f1">Terma Mata Ganjaran</a>.
            </p>

            <a href="{{ url('/customer/rewards') }}" style="display:block;background:#6366f1;color:white;text-align:center;padding:14px;border-radius:10px;text-decoration:none;font-weight:600;font-size:15px">
                Lihat ganjaran
            </a>
        </div>

        <p style="text-align:center;font-size:12px;color:#9ca3af;margin-top:20px">Emel ini dihantar oleh BonusHub bagi pihak {{ $merchantName }}.</p>
    </div>
</body>
</html>
