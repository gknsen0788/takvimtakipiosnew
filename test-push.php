<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

use App\PushNotification;
use App\Database;

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1;

// Get owner details if exists
$ownerName = 'Gökhan Şen';
try {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id, full_name, email FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $userId]);
    $u = $stmt->fetch();
    if ($u) {
        $ownerName = $u['full_name'];
    }
} catch (\Throwable $e) {}

$sampleDetails = [
    'id' => 999,
    'user_id' => $userId,
    'owner_name' => $ownerName,
    'villa_name' => 'Villa Sunset Luxe 18',
    'customer_name' => 'Ahmet Yılmaz',
    'customer_phone' => '0532 123 45 67',
    'start_date' => '2026-09-15',
    'end_date' => date('Y-m-d', strtotime('+1 day')),
    'checkout_time' => '10:00',
    'remaining_payment_tr' => '12.500 ₺'
];

header('Content-Type: text/html; charset=utf-8');
echo "<div style='font-family:sans-serif; max-width:600px; margin:40px auto; padding:24px; border:1px solid #e2e8f0; border-radius:16px; background:#fff;'>";
echo "<h2 style='color:#1e293b; margin-top:0;'>📱 TakvimTakip Push Bildirim Test Modülü</h2>";
echo "<p style='color:#64748b; font-size:14px;'>Bu sayfa, OneSignal REST API üzerinden belirtilen kullanıcıya örnek bir <b>çıkış bildirimi push mesajı</b> gönderir.</p>";
echo "<hr style='border:0; border-top:1px solid #f1f5f9; margin:16px 0;'>";
echo "<b>Hedef Kullanıcı ID:</b> " . htmlspecialchars((string)$userId) . " (" . htmlspecialchars($ownerName) . ")<br><br>";

$result = PushNotification::sendCheckoutReminder($sampleDetails);

if ($result['success']) {
    echo "<div style='background:#ecfdf5; color:#065f46; padding:16px; border-radius:12px; font-weight:bold; margin-bottom:16px;'>";
    echo "✅ Test Push Bildirimi Başarıyla OneSignal'a İletildi!";
    echo "</div>";
    echo "<b>OneSignal Yanıtı:</b><br>";
    echo "<pre style='background:#f8fafc; padding:12px; border-radius:8px; font-size:12px; overflow:auto;'>" . htmlspecialchars(json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
} else {
    echo "<div style='background:#fef2f2; color:#991b1b; padding:16px; border-radius:12px; font-weight:bold; margin-bottom:16px;'>";
    echo "❌ Bildirim Gönderilemedi!";
    echo "</div>";
    echo "<b>Hata Detayı:</b> " . htmlspecialchars($result['error'] ?? 'Bilinmeyen hata') . "<br><br>";
    if (!empty($result['response'])) {
        echo "<b>OneSignal Yanıtı:</b><br>";
        echo "<pre style='background:#f8fafc; padding:12px; border-radius:8px; font-size:12px; overflow:auto;'>" . htmlspecialchars(json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    }
    echo "<p style='font-size:13px; color:#475569;'><b>Çözüm:</b> <code>config/app.php</code> dosyasındaki <code>ONESIGNAL_APP_ID</code> ve <code>ONESIGNAL_REST_API_KEY</code> değerlerinin OneSignal panelinizden alınıp eksiksiz tanımlandığından emin olun.</p>";
}

echo "</div>";
