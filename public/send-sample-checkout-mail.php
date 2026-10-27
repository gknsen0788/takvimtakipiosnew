<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

use App\Mailer;

$toEmail = $_GET['to'] ?? 'gokhansen-1988@hotmail.com';

$sampleDetails = [
    'owner_email' => $toEmail,
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

$success = Mailer::sendCheckoutReminderToOwner($sampleDetails);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => $success,
    'sent_to' => $toEmail,
    'message' => $success ? "Örnek çıkış bildirim e-postası başarıyla {$toEmail} adresine gönderildi." : "E-posta gönderim hatası oluştu.",
    'sent_at' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
