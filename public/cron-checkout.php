<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

use App\Database;
use App\Mailer;
use App\Helpers;

// Initialize Database connection
$db = Database::getConnection();

// Ensure checkout_mail_sent_at column exists in reservations table
try {
    $db->exec("ALTER TABLE reservations ADD COLUMN checkout_mail_sent_at DATETIME NULL DEFAULT NULL;");
} catch (\Throwable $e) {
    // Column already exists
}

// Target date: tomorrow (guest checkout day)
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Fetch upcoming checkouts for tomorrow where reminder has not been sent yet
$stmt = $db->prepare("
    SELECT 
        r.*, 
        v.name AS villa_name, 
        v.checkout_time, 
        v.deposit_amount,
        u.full_name AS owner_name, 
        u.email AS owner_email
    FROM reservations r
    JOIN villas v ON r.villa_id = v.id
    JOIN users u ON v.user_id = u.id
    WHERE r.end_date = :tomorrow
      AND r.status IN ('booked', 'agency')
      AND r.checkout_mail_sent_at IS NULL
");

$stmt->execute(['tomorrow' => $tomorrow]);
$upcomingCheckouts = $stmt->fetchAll();

$sentCount = 0;
$sentDetails = [];
$errors = [];

foreach ($upcomingCheckouts as $item) {
    if (empty($item['owner_email'])) continue;

    $details = [
        'owner_email' => $item['owner_email'],
        'owner_name' => $item['owner_name'],
        'villa_name' => $item['villa_name'],
        'customer_name' => $item['customer_name'] ?: 'Belirtilmedi',
        'customer_phone' => $item['customer_phone'] ?: 'Belirtilmedi',
        'start_date_tr' => Helpers::formatDateTR($item['start_date']),
        'end_date_tr' => Helpers::formatDateTR($item['end_date']),
        'checkout_time' => $item['checkout_time'] ?: '10:00',
        'remaining_payment_tr' => !empty($item['remaining_payment']) ? (number_format((float)$item['remaining_payment'], 0, ',', '.') . ' ₺') : '0 ₺',
        'price_tr' => !empty($item['price']) ? (number_format((float)$item['price'], 0, ',', '.') . ' ₺') : '',
        'deposit_amount' => $item['deposit_amount'] ?: '',
        'note' => $item['note'] ?: ''
    ];

    $success = Mailer::sendCheckoutReminderToOwner($details);

    if ($success) {
        $upStmt = $db->prepare("UPDATE reservations SET checkout_mail_sent_at = CURRENT_TIMESTAMP WHERE id = :id");
        $upStmt->execute(['id' => $item['id']]);
        $sentCount++;
        $sentDetails[] = [
            'reservation_id' => $item['id'],
            'villa' => $item['villa_name'],
            'sent_to' => $item['owner_email'],
            'owner_name' => $item['owner_name']
        ];
    } else {
        $errors[] = "Reservation #{$item['id']} ({$item['owner_email']}) mail gönderimi başarısız oldu.";
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => "Çıkış hatırlatma cron görevi tamamlandı. Toplam {$sentCount} ev sahibine bildirim e-postası gönderildi.",
    'target_checkout_date' => $tomorrow,
    'sent_count' => $sentCount,
    'sent_details' => $sentDetails,
    'errors' => $errors,
    'executed_at' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
