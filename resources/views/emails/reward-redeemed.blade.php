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
                <span style="font-size:48px">🎁</span>
            </div>

            <h2 style="font-size:20px;color:#111827;text-align:center;margin:0 0 8px">Reward Redeemed!</h2>
            <p style="font-size:15px;color:#6b7280;text-align:center;margin:0 0 24px">
                Hi {{ $customerName }}, you've successfully redeemed a reward!
            </p>

            <div style="background:#fdf4ff;border:2px solid #e9d5ff;border-radius:12px;padding:16px;text-align:center;margin-bottom:24px">
                <div style="font-size:14px;color:#a855f7;margin-bottom:4px">Reward Claimed</div>
                <div style="font-size:20px;font-weight:700;color:#7c3aed">{{ $rewardName }}</div>
                <div style="font-size:13px;color:#6b21a8;margin-top:8px">
                    Points spent: <strong>-{{ number_format($pointsSpent) }} pts</strong>
                </div>
            </div>

            <div style="background:#f9fafb;border-radius:8px;padding:16px;margin-bottom:24px">
                <table style="width:100%;border-collapse:collapse;font-size:14px">
                    <tr>
                        <td style="padding:6px 0;color:#6b7280">Merchant</td>
                        <td style="padding:6px 0;color:#111827;font-weight:500;text-align:right">{{ $merchantName }}</td>
                    </tr>
                </table>
            </div>

            <a href="{{ url('/customer/rewards') }}" style="display:block;background:#6366f1;color:white;text-align:center;padding:14px;border-radius:10px;text-decoration:none;font-weight:600;font-size:15px">
                View My Rewards →
            </a>
        </div>

        <p style="font-size:12px;color:#9ca3af;text-align:center;margin-top:24px">
            BonusHub — Loyalty & Rewards Platform<br>
            <a href="{{ url('/') }}" style="color:#6366f1">bonushub.my</a>
        </p>
    </div>
</body>
</html>