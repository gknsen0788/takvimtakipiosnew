<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

use App\Mailer;

// Debounce lock: prevent duplicate execution within 10 seconds (e.g., from browser double GET / Go HTTP client retries)
$lockFile = __DIR__ . '/scratch/test_mail_debounce.lock';
$lastSent = @file_get_contents($lockFile);
$now = time();

if ($lastSent && ($now - (int)$lastSent) < 10) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h2>TakvimTakip Çıkış Bildirim E-Posta Test Modülü</h2>";
    echo "<h3 style='color:orange;'>⚠️ Mükerrer Gönderim Engellendi: 10 saniye içinde tekrarlanan istek durduruldu.</h3>";
    exit;
}

@file_put_contents($lockFile, (string)$now);

$logFile = __DIR__ . '/scratch/mail_execution.log';
$logEntry = date('Y-m-d H:i:s') . ' [' . microtime(true) . '] URI: ' . ($_SERVER['REQUEST_URI'] ?? 'CLI') . ' User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'None') . "\n";
@file_put_contents($logFile, $logEntry, FILE_APPEND);

$to = $_GET['to'] ?? 'gokhansen-1988@hotmail.com';

$sampleDetails = [
    'owner_email' => $to,
    'owner_name' => 'Gökhan Şen',
    'villa_name' => 'Villa Sunset Luxe 18',
    'customer_name' => 'Ahmet Yılmaz',
    'customer_phone' => '0532 123 45 67',
    'start_date_tr' => '15.09.2026',
    'end_date_tr' => date('d.m.Y', strtotime('+1 day')),
    'checkout_time' => '10:00',
    'remaining_payment_tr' => '12.500 ₺',
    'price_tr' => '25.000 ₺',
    'deposit_amount' => '2.000 ₺ Hasar Depozitosu',
    'note' => '2 yetişkin 1 çocuk aile. Çocuk yatağı ve mama sandalyesi hazırlandı.'
];

echo "<h2>TakvimTakip Çıkış Bildirim E-Posta Test Modülü</h2>";
echo "<b>Alıcı E-posta:</b> " . htmlspecialchars($to) . "<br><hr>";

$success = Mailer::sendCheckoutReminderToOwner($sampleDetails);

if ($success) {
    echo "<h3 style='color:green;'>✅ Örnek Çıkış Bildirim E-Postası Başarıyla " . htmlspecialchars($to) . " Adresine Gönderildi!</h3>";
} else {
    echo "<h3 style='color:red;'>❌ E-Posta Gönderimi Başarısız Oldu!</h3>";
}
