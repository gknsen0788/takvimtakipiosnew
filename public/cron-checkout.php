<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

use App\Database;
use App\PushNotification;
use App\WebPush;
use App\Helpers;

// Initialize Database connection
$db = Database::getConnection();

// Web tarayıcısı üzerinden çağrılıyorsa Cron Secret Key veya Admin yetkisi doğrula
if (php_sapi_name() !== 'cli') {
    $providedKey = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_SECRET'] ?? '';
    if ($providedKey !== CRON_SECRET && !\App\Auth::isAdmin()) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Forbidden: Geçersiz Cron Güvenlik Anahtarı (key)']);
        exit;
    }
}

// Ensure checkout_push_sent_at column exists in reservations table
try {
    $db->exec("ALTER TABLE reservations ADD COLUMN checkout_push_sent_at DATETIME NULL;");
} catch (\Throwable $e) {}

// Target date: tomorrow (guest checkout day)
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Test modunda veya zorla tekrar gönderimde (&force=1) daha önce gönderilmiş olanları da dahil et
$force = isset($_GET['force']) && $_GET['force'] == '1';
$sentFilter = $force ? "" : "AND r.checkout_push_sent_at IS NULL";

// Fetch upcoming checkouts for tomorrow (booked, agency, blocked)
$stmt = $db->prepare("
    SELECT 
        r.*, 
        v.name AS villa_name, 
        v.checkout_time, 
        v.deposit_amount,
        u.id AS user_id,
        u.full_name AS owner_name, 
        u.email AS owner_email
    FROM reservations r
    JOIN villas v ON r.villa_id = v.id
    JOIN users u ON v.user_id = u.id
    WHERE r.end_date = :tomorrow
      AND r.status != 'cancelled'
      $sentFilter
");

$stmt->execute(['tomorrow' => $tomorrow]);
$upcomingCheckouts = $stmt->fetchAll();

$sentCount = 0;
$sentDetails = [];
$errors = [];

foreach ($upcomingCheckouts as $item) {
    if (empty($item['user_id'])) continue;

    $details = [
        'id' => $item['id'],
        'user_id' => $item['user_id'],
        'owner_name' => $item['owner_name'],
        'owner_email' => $item['owner_email'],
        'villa_name' => $item['villa_name'],
        'customer_name' => $item['customer_name'] ?: 'Belirtilmedi',
        'customer_phone' => $item['customer_phone'] ?: 'Belirtilmedi',
        'start_date' => $item['start_date'],
        'end_date' => $item['end_date'],
        'start_date_tr' => Helpers::formatDateTR($item['start_date']),
        'end_date_tr' => Helpers::formatDateTR($item['end_date']),
        'checkout_time' => $item['checkout_time'] ?: '10:00',
        'remaining_payment_tr' => !empty($item['remaining_payment']) ? (number_format((float)$item['remaining_payment'], 0, ',', '.') . ' ₺') : '0 ₺',
        'price_tr' => !empty($item['price']) ? (number_format((float)$item['price'], 0, ',', '.') . ' ₺') : '',
        'deposit_amount' => $item['deposit_amount'] ?: '',
        'note' => $item['note'] ?: ''
    ];

    // 1. OneSignal Native Push (Google Play Store & Apple App Store)
    $oneSignalRes = PushNotification::sendCheckoutReminder($details);

    // 2. Web Push (Masaüstü & PWA Tarayıcıları)
    $webPushRes = WebPush::sendCheckoutReminder($details);

    if ($oneSignalRes['success'] || $webPushRes['success']) {
        $upStmt = $db->prepare("UPDATE reservations SET checkout_push_sent_at = CURRENT_TIMESTAMP WHERE id = :id");
        $upStmt->execute(['id' => $item['id']]);
        $sentCount++;
        $sentDetails[] = [
            'reservation_id' => $item['id'],
            'villa' => $item['villa_name'],
            'user_id' => $item['user_id'],
            'owner_name' => $item['owner_name'],
            'onesignal_ok' => $oneSignalRes['success'],
            'webpush_ok' => $webPushRes['success']
        ];
    } else {
        $errors[] = "Rezervasyon #{$item['id']} ({$item['owner_name']}) bildirim iletilemedi: " . ($oneSignalRes['error'] ?? $webPushRes['error'] ?? 'Bilinmeyen hata');
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'channel' => 'hybrid_push (onesignal_native + web_push)',
    'message' => "Çıkış hatırlatma push bildirimi cron görevi tamamlandı. Toplam {$sentCount} ev sahibine bildirim iletildi.",
    'target_checkout_date' => $tomorrow,
    'sent_count' => $sentCount,
    'sent_details' => $sentDetails,
    'errors' => $errors,
    'executed_at' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
