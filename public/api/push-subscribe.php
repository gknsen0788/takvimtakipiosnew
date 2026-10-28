<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';

use App\Auth;
use App\WebPush;

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Oturum açılmadı.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode((string)$rawInput, true);

if (empty($input['endpoint'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Eksik endpoint bilgisi.']);
    exit;
}

if (isset($input['action']) && $input['action'] === 'unsubscribe') {
    WebPush::deleteSubscription($input['endpoint']);
    echo json_encode(['success' => true, 'action' => 'unsubscribed']);
    exit;
}

$p256dh = $input['keys']['p256dh'] ?? '';
$auth = $input['keys']['auth'] ?? '';

if (empty($p256dh) || empty($auth)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Eksik şifreleme anahtarları (p256dh, auth).']);
    exit;
}

$saved = WebPush::saveSubscription(
    Auth::id(),
    $input['endpoint'],
    $p256dh,
    $auth,
    $_SERVER['HTTP_USER_AGENT'] ?? null
);

echo json_encode([
    'success' => $saved,
    'user_id' => Auth::id(),
    'message' => $saved ? 'Abonelik başarıyla kaydedildi.' : 'Abonelik kaydedilemedi.'
]);
exit;
