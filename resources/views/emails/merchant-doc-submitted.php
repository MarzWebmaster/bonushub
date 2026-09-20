<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif;background:#f4f4f5;margin:0;padding:0}.container{max-width:600px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08)}.header{background:linear-gradient(135deg,#6366f1,#a855f7);padding:24px;text-align:center;color:#fff}.header h1{margin:0;font-size:20px}.body{padding:24px}.info-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f3f4f6}.info-label{color:#6b7280;font-size:13px}.info-value{font-weight:600;font-size:14px;color:#111827}.docs{margin-top:16px}.doc-tag{display:inline-block;padding:6px 14px;background:#6366f1;color:#fff;border-radius:6px;font-size:12px;margin:3px}.footer{padding:16px 24px;text-align:center;font-size:12px;color:#9ca3af;border-top:1px solid #f3f4f6}</style></head>
<body>
<div class="container">
    <div class="header">
        <h1>Dokumen Merchant Baru</h1>
        <p style="margin:4px 0 0;opacity:0.9;font-size:14px">Pengesahan dokumen baru dihantar</p>
    </div>
    <div class="body">
        <div class="info-row"><span class="info-label">Nama Syarikat</span><span class="info-value"><?= e($merchantName) ?></span></div>
        <div class="info-row"><span class="info-label">Pemilik</span><span class="info-value"><?= e($ownerName) ?></span></div>
        <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?= e($email) ?></span></div>
        <div class="info-row"><span class="info-label">Telefon</span><span class="info-value"><?= e($phone) ?></span></div>
        <div class="docs">
            <p style="font-weight:600;font-size:14px;margin-bottom:8px">Dokumen Dilampirkan:</p>
            <?php if($icFront): ?><span class="doc-tag">IC Depan</span><?php endif; ?>
            <?php if($icBack): ?><span class="doc-tag">IC Belakang</span><?php endif; ?>
            <?php if($ssmImage): ?><span class="doc-tag">SSM</span><?php endif; ?>
        </div>
        <p style="margin-top:20px;font-size:13px;color:#6b7280">Sila semak dan luluskan di <a href="https://bonushub.my/superadmin/merchants" style="color:#6366f1">Panel Superadmin</a>.</p>
    </div>
    <div class="footer">BonusHub Loyalty System &copy; 2026 Marz Technology</div>
</div>
</body>
</html>
