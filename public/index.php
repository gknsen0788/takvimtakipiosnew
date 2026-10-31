<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

use App\Auth;
use App\Database;
use App\Helpers;
use App\IcalEngine;
use App\Models\Villa;
use App\Models\Reservation;
use App\Models\PricePeriod;
use App\Models\ExternalCalendar;
use App\Models\User;
use App\Mailer;

// Global HTTP Security Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:; img-src 'self' data: https: http:;");
header_remove('X-Powered-By');

// Ensure DB is initialized
Database::getConnection();

$rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
if ($scriptDir === '.' || $scriptDir === '\\') {
    $scriptDir = '';
}
$baseAppDir = $scriptDir;
if (str_ends_with($scriptDir, '/public')) {
    $baseAppDir = substr($scriptDir, 0, -7);
}
if ($baseAppDir !== '' && $baseAppDir !== '/' && str_starts_with($rawUri, $baseAppDir)) {
    $rawUri = substr($rawUri, strlen($baseAppDir));
}
if (str_starts_with($rawUri, '/public')) {
    $rawUri = substr($rawUri, 7);
}
$uri = '/' . trim($rawUri, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// -------------------------------------------------------------
// PWA & SERVICE WORKER PUBLIC ROUTES
// -------------------------------------------------------------
if ($uri === '/manifest.json') {
    header('Content-Type: application/manifest+json; charset=utf-8');
    if (file_exists(__DIR__ . '/manifest.json')) {
        readfile(__DIR__ . '/manifest.json');
    }
    exit;
}

if ($uri === '/sw.js') {
    header('Content-Type: application/javascript; charset=utf-8');
    if (file_exists(__DIR__ . '/sw.js')) {
        readfile(__DIR__ . '/sw.js');
    }
    exit;
}

if ($uri === '/.well-known/assetlinks.json') {
    header('Content-Type: application/json; charset=utf-8');
    if (file_exists(__DIR__ . '/.well-known/assetlinks.json')) {
        readfile(__DIR__ . '/.well-known/assetlinks.json');
    }
    exit;
}

// -------------------------------------------------------------
// PUBLIC ICAL EXPORT & CRON SYNC ROUTES
// -------------------------------------------------------------
if (preg_match('#^/api/calendar/([a-zA-Z0-9_]+)\.ics$#', $uri, $matches)) {
    $token = $matches[1];
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="calendar.ics"');
    echo IcalEngine::exportIcs($token);
    exit;
}

if ($uri === '/cron/sync-ical' || $uri === '/api/cron/sync-ical') {
    $providedKey = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_SECRET'] ?? '';
    if ($providedKey !== CRON_SECRET && !Auth::isAdmin()) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Forbidden: Invalid Cron Secret Key']);
        exit;
    }
    ExternalCalendar::syncAll();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'synced_at' => date('Y-m-d H:i:s')]);
    exit;
}

if ($uri === '/cron/send-checkout-reminders' || $uri === '/cron-checkout') {
    $providedKey = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_SECRET'] ?? '';
    if ($providedKey !== CRON_SECRET && !Auth::isAdmin()) {
        http_response_code(403);
        echo "Forbidden: Invalid Cron Secret Key";
        exit;
    }
    require_once __DIR__ . '/../cron-checkout.php';
    exit;
}

// -------------------------------------------------------------
// NATIVE WEB PUSH SUBSCRIPTION ROUTES
// -------------------------------------------------------------
if ($uri === '/api/push-subscribe' || $uri === '/api/push-subscribe.php') {
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
        \App\WebPush::deleteSubscription($input['endpoint']);
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

    $saved = \App\WebPush::saveSubscription(
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
}

if ($uri === '/api/push-vapid-public-key') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'publicKey' => \App\WebPush::getPublicKey()
    ]);
    exit;
}

if ($uri === '/send-sample-checkout-mail') {
    Auth::requireAdmin();
    require_once __DIR__ . '/../send-sample-checkout-mail.php';
    exit;
}

// -------------------------------------------------------------
// PUBLIC VILLA PAGE & PUBLIC CALENDAR API
// -------------------------------------------------------------
if (preg_match('#^/villa/([a-zA-Z0-9\-]+)$#', $uri, $matches)) {
    $slug = $matches[1];
    $villa = Villa::findBySlug($slug);
    if (!$villa) {
        http_response_code(404);
        echo "<h1>404 - Villa Bulunamadı</h1>";
        exit;
    }

    // Get owner phone for WhatsApp CTA
    $db = Database::getConnection();
    $ownerStmt = $db->prepare("SELECT phone FROM users WHERE id = :user_id");
    $ownerStmt->execute(['user_id' => $villa['user_id']]);
    $ownerPhone = $ownerStmt->fetchColumn() ?: '';

    // Get price periods and villa images for public view modal
    $pricePeriods = PricePeriod::getByVilla($villa['id']);
    $villaImages = Villa::getImages($villa['id']);

    require __DIR__ . '/../views/public/villa.php';
    exit;
}

if (preg_match('#^/api/public/villas/([0-9]+)/calendar$#', $uri, $matches)) {
    $villaId = (int)$matches[1];
    $start = $_GET['start'] ?? date('Y-m-01');
    $end = $_GET['end'] ?? date('Y-m-t');
    Helpers::jsonResponse(Reservation::getPublicCalendarEvents($villaId, $start, $end));
}

// -------------------------------------------------------------
// AUTH ROUTES
// -------------------------------------------------------------
if ($uri === '/login') {
    if (Auth::check()) {
        Helpers::redirect('/dashboard');
    }
    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Token Mismatch');
        $remember = isset($_POST['remember_me']);
        if (Auth::login($_POST['email'] ?? '', $_POST['password'] ?? '', $remember)) {
            Helpers::redirect('/dashboard');
        } else {
            Helpers::redirect('/login');
        }
    }
    require __DIR__ . '/../views/auth/login.php';
    exit;
}

if ($uri === '/register') {
    if (Auth::check()) {
        Helpers::redirect('/dashboard');
    }
    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Token Mismatch');
        if (Auth::register($_POST['full_name'] ?? '', $_POST['email'] ?? '', $_POST['phone'] ?? '', $_POST['password'] ?? '')) {
            Helpers::redirect('/dashboard');
        } else {
            Helpers::redirect('/register');
        }
    }
    require __DIR__ . '/../views/auth/register.php';
    exit;
}

if ($uri === '/forgot-password') {
    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Token Mismatch');
        $res = Auth::createPasswordResetToken($_POST['email_or_phone'] ?? '');
        if ($res['success']) {
            $mailSent = Mailer::sendPasswordReset($res['email'], $res['full_name'], $res['reset_url']);
            if ($mailSent) {
                Helpers::setFlash('success', 'Şifre sıfırlama bağlantısı e-posta adresinize (' . Helpers::sanitize($res['email']) . ') gönderildi. Lütfen e-posta (ve Spam/Junk) klasörünüzü kontrol ediniz.');
            } else {
                Helpers::setFlash('error', 'E-posta gönderimi esnasında sunucu kaynaklı bir hata oluştu. Lütfen tekrar deneyiniz.');
            }
        } else {
            Helpers::setFlash('error', $res['message']);
        }
        Helpers::redirect('/forgot-password');
    }
    require __DIR__ . '/../views/auth/forgot_password.php';
    exit;
}

if ($uri === '/reset-password') {
    $token = $_GET['token'] ?? $_POST['token'] ?? '';
    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Token Mismatch');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if ($password !== $confirm) {
            Helpers::setFlash('error', 'Şifreler birbiriyle uyuşmuyor.');
        } else {
            $res = Auth::resetPasswordWithToken($token, $password);
            if ($res['success']) {
                Helpers::setFlash('success', $res['message']);
                Helpers::redirect('/login');
            } else {
                Helpers::setFlash('error', $res['message']);
            }
        }
    }
    
    // Validate token exists
    $resetRecord = Auth::validatePasswordResetToken($token);
    if (!$resetRecord && $method === 'GET') {
        Helpers::setFlash('error', 'Geçersiz veya süresi dolmuş sıfırlama bağlantısı.');
        Helpers::redirect('/forgot-password');
    }

    require __DIR__ . '/../views/auth/reset_password.php';
    exit;
}

if ($uri === '/logout') {
    Auth::logout();
    Helpers::redirect('/login');
}

// -------------------------------------------------------------
// PUBLIC LEGAL & INSTITUTIONAL ROUTES (App Store / KVKK)
// -------------------------------------------------------------
if ($uri === '/gizlilik' || $uri === '/privacy') {
    require __DIR__ . '/../views/legal/privacy.php';
    exit;
}

if ($uri === '/kullanim-kosullari' || $uri === '/terms') {
    require __DIR__ . '/../views/legal/terms.php';
    exit;
}

if ($uri === '/kvkk') {
    require __DIR__ . '/../views/legal/kvkk.php';
    exit;
}

if ($uri === '/iletisim' || $uri === '/contact') {
    require __DIR__ . '/../views/legal/contact.php';
    exit;
}

// -------------------------------------------------------------
// PUBLIC LANDING PAGE ROUTE
// -------------------------------------------------------------
if ($uri === '/' || $uri === '') {
    if (Auth::check()) {
        Helpers::redirect('/dashboard');
    }
    require __DIR__ . '/../views/landing/index.php';
    exit;
}

// -------------------------------------------------------------
// AUTHENTICATED ROUTES
// -------------------------------------------------------------
Auth::requireAuth();
$user = Auth::user();

if ($uri === '/profile') {
    if (Auth::isAdmin()) {
        Helpers::setFlash('error', 'Hesap bilgileri menüsü yönetici hesapları için geçerli değildir.');
        Helpers::redirect('/dashboard');
    }

    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');

        $action = $_POST['action'] ?? '';
        if ($action === 'update_profile') {
            $res = User::updateProfile($user['id'], $_POST);
            if ($res['success']) {
                Helpers::setFlash('success', $res['message']);
            } else {
                Helpers::setFlash('error', $res['message']);
            }
        } elseif ($action === 'update_password') {
            $currentPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            $res = User::updatePassword($user['id'], $currentPass, $newPass, $confirmPass);
            if ($res['success']) {
                Helpers::setFlash('success', $res['message']);
            } else {
                Helpers::setFlash('error', $res['message']);
            }
        }
        Helpers::redirect('/profile');
    }

    $profileUser = User::findById($user['id']);
    require __DIR__ . '/../views/auth/profile.php';
    exit;
}

if ($uri === '/profile/delete-account' && $method === 'POST') {
    if (Auth::isAdmin()) {
        Helpers::setFlash('error', 'Yönetici hesapları bu alandan silinemez.');
        Helpers::redirect('/dashboard');
    }

    if (!Helpers::verifyCsrf()) die('CSRF Mismatch');

    $res = User::deleteOwnAccount($user['id']);
    if ($res['success']) {
        Helpers::setFlash('success', $res['message']);
        Helpers::redirect('/login');
    } else {
        Helpers::setFlash('error', $res['message']);
        Helpers::redirect('/profile');
    }
}

if ($uri === '/dashboard') {
    $db = Database::getConnection();
    
    // Stats calculation (Active villas only for dashboard)
    if (Auth::isAdmin()) {
        $villas = Villa::allForAdmin(true);
    } else {
        $villas = Villa::allByUser($user['id'], true);
    }
    $totalVillas = count($villas);

    // Available today - Optimized 1 SQL query
    $today = date('Y-m-d');
    if (Auth::isAdmin()) {
        $availStmt = $db->prepare("
            SELECT COUNT(*) FROM villas v 
            WHERE v.status = 'active' 
              AND NOT EXISTS (
                  SELECT 1 FROM reservations r 
                  WHERE r.villa_id = v.id 
                    AND r.status IN ('booked', 'blocked') 
                    AND (:today >= r.start_date AND :today < r.end_date)
              )
        ");
        $availStmt->execute(['today' => $today]);
    } else {
        $availStmt = $db->prepare("
            SELECT COUNT(*) FROM villas v 
            WHERE v.status = 'active' 
              AND v.user_id = :user_id 
              AND NOT EXISTS (
                  SELECT 1 FROM reservations r 
                  WHERE r.villa_id = v.id 
                    AND r.status IN ('booked', 'blocked') 
                    AND (:today >= r.start_date AND :today < r.end_date)
              )
        ");
        $availStmt->execute(['user_id' => $user['id'], 'today' => $today]);
    }
    $availCount = (int)$availStmt->fetchColumn();

    // Monthly bookings
    $monthStart = date('Y-m-01');
    if (Auth::isAdmin()) {
        $monthlyStmt = $db->prepare("
            SELECT COUNT(*) FROM reservations r
            JOIN villas v ON r.villa_id = v.id
            WHERE v.status = 'active' AND r.status = 'booked' AND r.created_at >= :mstart
        ");
        $monthlyStmt->execute(['mstart' => $monthStart]);
    } else {
        $monthlyStmt = $db->prepare("
            SELECT COUNT(*) FROM reservations r
            JOIN villas v ON r.villa_id = v.id
            WHERE v.user_id = :user_id AND v.status = 'active' AND r.status = 'booked' AND r.created_at >= :mstart
        ");
        $monthlyStmt->execute(['user_id' => $user['id'], 'mstart' => $monthStart]);
    }
    $monthlyBookings = (int)$monthlyStmt->fetchColumn();

    // Upcoming moves (checkin/checkout in next 7 days)
    $next7Days = date('Y-m-d', strtotime('+7 days'));
    if (Auth::isAdmin()) {
        $movesStmt = $db->prepare("
            SELECT r.*, v.name as villa_name, u.full_name as owner_name FROM reservations r
            JOIN villas v ON r.villa_id = v.id
            JOIN users u ON v.user_id = u.id
            WHERE v.status = 'active'
              AND r.status = 'booked'
              AND r.start_date BETWEEN :today AND :next7
            ORDER BY r.start_date ASC
            LIMIT 10
        ");
        $movesStmt->execute(['today' => $today, 'next7' => $next7Days]);
    } else {
        $movesStmt = $db->prepare("
            SELECT r.*, v.name as villa_name FROM reservations r
            JOIN villas v ON r.villa_id = v.id
            WHERE v.user_id = :user_id 
              AND v.status = 'active'
              AND r.status = 'booked'
              AND r.start_date BETWEEN :today AND :next7
            ORDER BY r.start_date ASC
            LIMIT 10
        ");
        $movesStmt->execute(['user_id' => $user['id'], 'today' => $today, 'next7' => $next7Days]);
    }
    $upcomingMoves = $movesStmt->fetchAll();

    // Pre-fetch all active reservations for dashboard in 1 fast query
    if (Auth::isAdmin()) {
        $allResStmt = $db->prepare("
            SELECT r.*, v.name as villa_name, v.city as villa_city, v.district as villa_district, v.checkin_time, v.checkout_time, v.google_drive_link, v.deposit_amount as villa_deposit 
            FROM reservations r
            JOIN villas v ON r.villa_id = v.id
            WHERE v.status = 'active'
            ORDER BY r.start_date DESC, r.id DESC
        ");
        $allResStmt->execute();
    } else {
        $allResStmt = $db->prepare("
            SELECT r.*, v.name as villa_name, v.city as villa_city, v.district as villa_district, v.checkin_time, v.checkout_time, v.google_drive_link, v.deposit_amount as villa_deposit 
            FROM reservations r
            JOIN villas v ON r.villa_id = v.id
            WHERE v.user_id = :user_id AND v.status = 'active'
            ORDER BY r.start_date DESC, r.id DESC
        ");
        $allResStmt->execute(['user_id' => $user['id']]);
    }
    $allReservations = $allResStmt->fetchAll(PDO::FETCH_ASSOC);

    // Compute Today's Check-ins & Check-outs directly from $allReservations in PHP (100% reliable format handling)
    $today = date('Y-m-d');
    $todayCheckins = [];
    $todayCheckouts = [];

    foreach ($allReservations as $res) {
        $sRaw = trim((string)($res['start_date'] ?? ''));
        if (!empty($sRaw)) {
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})/', $sRaw, $m)) {
                $sFormatted = "{$m[3]}-{$m[2]}-{$m[1]}";
            } else {
                $sTime = strtotime($sRaw);
                $sFormatted = ($sTime !== false) ? date('Y-m-d', $sTime) : substr($sRaw, 0, 10);
            }
            if ($sFormatted === $today) {
                $todayCheckins[] = $res;
            }
        }

        $eRaw = trim((string)($res['end_date'] ?? ''));
        if (!empty($eRaw)) {
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})/', $eRaw, $m)) {
                $eFormatted = "{$m[3]}-{$m[2]}-{$m[1]}";
            } else {
                $eTime = strtotime($eRaw);
                $eFormatted = ($eTime !== false) ? date('Y-m-d', $eTime) : substr($eRaw, 0, 10);
            }
            if ($eFormatted === $today) {
                $todayCheckouts[] = $res;
            }
        }
    }

    $stats = [
        'total_villas' => $totalVillas,
        'available_today' => $availCount,
        'monthly_bookings' => $monthlyBookings
    ];

    require __DIR__ . '/../views/dashboard/index.php';
    exit;
}

// -------------------------------------------------------------
// VILLAS CONTROLLER ROUTES
// -------------------------------------------------------------
if ($uri === '/calendar-management') {
    if (Auth::isAdmin()) {
        $villas = Villa::allForAdmin(true);
    } else {
        $villas = Villa::allByUser($user['id'], true);
    }
    require __DIR__ . '/../views/villas/calendar_select.php';
    exit;
}

if ($uri === '/occupancy-calendar') {
    if (Auth::isAdmin()) {
        $villas = Villa::allForAdmin(true);
    } else {
        $villas = Villa::allByUser($user['id'], true);
    }

    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
    if ($month < 1 || $month > 12) $month = (int)date('m');
    if ($year < 2020 || $year > 2035) $year = (int)date('Y');

    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));

    $db = Database::getConnection();
    $reservations = [];
    if (!empty($villas)) {
        $villaIds = array_column($villas, 'id');
        $inClause = implode(',', array_map('intval', $villaIds));
        $stmt = $db->query("
            SELECT r.*, v.name as villa_name
            FROM reservations r
            JOIN villas v ON r.villa_id = v.id
            WHERE r.villa_id IN ({$inClause})
              AND r.start_date <= '{$endDate}'
              AND r.end_date >= '{$startDate}'
            ORDER BY r.start_date ASC
        ");
        $reservations = $stmt->fetchAll();
    }

    $daysInMonth = (int)date('t', strtotime($startDate));

    require __DIR__ . '/../views/villas/occupancy_calendar.php';
    exit;
}

if ($uri === '/villas') {
    if (Auth::isAdmin()) {
        $villas = Villa::allForAdmin();
    } else {
        $villas = Villa::allByUser($user['id']);
    }
    require __DIR__ . '/../views/villas/index.php';
    exit;
}

if ($uri === '/villas/new') {
    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');
        $villaId = Villa::create($_POST, $user['id']);
        if ($villaId) {
            Helpers::setFlash('success', 'Villa başarıyla eklendi.');
            Helpers::redirect("/villas/{$villaId}/calendar");
        } else {
            Helpers::redirect('/villas/new');
        }
    }
    require __DIR__ . '/../views/villas/create.php';
    exit;
}

if (preg_match('#^/villas/([0-9]+)/edit$#', $uri, $matches)) {
    $id = (int)$matches[1];
    $villa = Villa::find($id, $user['id'], Auth::isAdmin());
    if (!$villa) {
        Helpers::setFlash('error', 'Villa bulunamadı.');
        Helpers::redirect('/villas');
    }

    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');
        if (Villa::update($id, $_POST, $user['id'], Auth::isAdmin())) {
            Helpers::setFlash('success', 'Villa detayları güncellendi.');
            Helpers::redirect("/villas/{$id}/edit");
        }
    }

    require __DIR__ . '/../views/villas/edit.php';
    exit;
}

if (preg_match('#^/villas/([0-9]+)/delete$#', $uri, $matches)) {
    $id = (int)$matches[1];
    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');
        if (Villa::delete($id, $user['id'], Auth::isAdmin())) {
            Helpers::setFlash('success', 'Konut başarıyla silindi.');
        } else {
            Helpers::setFlash('error', 'Konut silinirken bir hata oluştu.');
        }
    }
    Helpers::redirect('/villas');
}

if (preg_match('#^/villas/([0-9]+)/upload-photo$#', $uri, $matches)) {
    $id = (int)$matches[1];
    $villa = Villa::find($id, $user['id'], Auth::isAdmin());
    
    if (!$villa) {
        Helpers::setFlash('error', 'Villa bulunamadı.');
        Helpers::redirect('/villas');
    }

    if (empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
        Helpers::setFlash('error', 'Yüklenen fotoğraf boyutu PHP (post_max_size) limitini aşıyor.');
    } else if (!isset($_FILES['photo'])) {
        Helpers::setFlash('error', 'Lütfen bir fotoğraf dosyası seçin.');
    } else if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errCode = $_FILES['photo']['error'];
        $errMsg = match($errCode) {
            UPLOAD_ERR_INI_SIZE => 'Fotoğraf boyutu sunucu limitini (upload_max_filesize) aşıyor.',
            UPLOAD_ERR_FORM_SIZE => 'Fotoğraf boyutu izin verilen limiti aşıyor.',
            UPLOAD_ERR_PARTIAL => 'Fotoğraf tam yüklenemedi, tekrar deneyin.',
            UPLOAD_ERR_NO_FILE => 'Lütfen bir fotoğraf seçin.',
            default => 'Fotoğraf yüklenirken hata oluştu (Kod: ' . $errCode . ').'
        };
        Helpers::setFlash('error', $errMsg);
    } else {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'jfif', 'gif'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/x-icon'];
        
        $mimeType = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            finfo_close($finfo);
        } else if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($_FILES['photo']['tmp_name']) ?: '';
        }

        if (!in_array($ext, $allowedExts) || ($mimeType && !in_array($mimeType, $allowedMimes))) {
            Helpers::setFlash('error', 'Geçersiz dosya formatı veya MIME türü. İzin verilenler: JPG, JPEG, PNG, WEBP, GIF.');
        } else {
            if (!file_exists(UPLOAD_DIR)) {
                @mkdir(UPLOAD_DIR, 0777, true);
            }
            $filename = 'villa_' . $id . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $targetPath = UPLOAD_DIR . $filename;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                $imgUrl = UPLOAD_URL . $filename;
                // Single cover photo: Delete existing photo(s) first
                if (!empty($villa['images'])) {
                    foreach ($villa['images'] as $oldImg) {
                        Villa::deleteImage((int)$oldImg['id'], $id, $user['id'], Auth::isAdmin());
                    }
                }
                Villa::addImage($id, $imgUrl, true);
                Helpers::setFlash('success', 'Kapak fotoğrafı başarıyla yüklendi.');
            } else {
                Helpers::setFlash('error', 'Fotoğraf sunucu klasörüne yazılamadı (' . UPLOAD_DIR . '). Klasör izinlerini kontrol edin.');
            }
        }
    }
    Helpers::redirect("/villas/{$id}/edit");
}

if (preg_match('#^/villas/([0-9]+)/delete-photo/([0-9]+)$#', $uri, $matches)) {
    $villaId = (int)$matches[1];
    $imageId = (int)$matches[2];
    if (Helpers::verifyCsrf()) {
        if (Villa::deleteImage($imageId, $villaId, $user['id'], Auth::isAdmin())) {
            Helpers::setFlash('success', 'Fotoğraf silindi.');
        } else {
            Helpers::setFlash('error', 'Fotoğraf silinirken hata oluştu.');
        }
    }
    Helpers::redirect("/villas/{$villaId}/edit");
}

if (preg_match('#^/villas/([0-9]+)/set-cover/([0-9]+)$#', $uri, $matches)) {
    $villaId = (int)$matches[1];
    $imageId = (int)$matches[2];
    if (Helpers::verifyCsrf()) {
        if (Villa::setCoverImage($imageId, $villaId, $user['id'], Auth::isAdmin())) {
            Helpers::setFlash('success', 'Kapak fotoğrafı güncellendi.');
        }
    }
    Helpers::redirect("/villas/{$villaId}/edit");
}

if (preg_match('#^/villas/([0-9]+)/duplicate$#', $uri, $matches)) {
    $id = (int)$matches[1];
    Villa::duplicate($id, $user['id'], Auth::isAdmin());
    Helpers::redirect('/villas');
}

if (preg_match('#^/villas/([0-9]+)/copy-prices$#', $uri, $matches)) {
    $targetVillaId = (int)$matches[1];
    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');
        $sourceVillaId = (int)($_POST['source_villa_id'] ?? 0);
        PricePeriod::copyFromVilla($targetVillaId, $sourceVillaId, $user['id'], Auth::isAdmin());
    }
    Helpers::redirect('/villas');
}

if (preg_match('#^/api/villas/([0-9]+)/toggle-status$#', $uri, $matches)) {
    $id = (int)$matches[1];
    $newStatus = Villa::toggleStatus($id, $user['id'], Auth::isAdmin());
    if ($newStatus !== null) {
        Helpers::jsonResponse(['success' => true, 'new_status' => $newStatus]);
    } else {
        Helpers::jsonResponse(['success' => false, 'message' => 'Yetkisiz veya geçersiz işlem.'], 403);
    }
}

if (preg_match('#^/villas/([0-9]+)/calendar$#', $uri, $matches)) {
    $id = (int)$matches[1];
    $villa = Villa::find($id, $user['id'], Auth::isAdmin());
    if (!$villa) {
        Helpers::setFlash('error', 'Villa bulunamadı.');
        Helpers::redirect('/villas');
    }
    require __DIR__ . '/../views/villas/calendar.php';
    exit;
}

if (preg_match('#^/villas/([0-9]+)/pricing$#', $uri, $matches)) {
    $id = (int)$matches[1];
    $villa = Villa::find($id, $user['id'], Auth::isAdmin());
    if (!$villa) Helpers::redirect('/villas');

    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');
        $_POST['villa_id'] = $id;
        PricePeriod::create($_POST, $user['id']);
        Helpers::setFlash('success', 'Dönemsel fiyat eklendi.');
        Helpers::redirect("/villas/{$id}/pricing");
    }

    $pricePeriods = PricePeriod::getByVilla($id);
    require __DIR__ . '/../views/villas/pricing.php';
    exit;
}

if (preg_match('#^/villas/([0-9]+)/pricing/delete/([0-9]+)$#', $uri, $matches)) {
    $villaId = (int)$matches[1];
    $priceId = (int)$matches[2];
    PricePeriod::delete($priceId, $user['id']);
    Helpers::setFlash('success', 'Fiyat dönemi silindi.');
    Helpers::redirect("/villas/{$villaId}/pricing");
}

if (preg_match('#^/villas/([0-9]+)/ical$#', $uri, $matches)) {
    $id = (int)$matches[1];
    $villa = Villa::find($id, $user['id'], Auth::isAdmin());
    if (!$villa) Helpers::redirect('/villas');

    $externalCalendars = ExternalCalendar::getByVilla($id);
    require __DIR__ . '/../views/villas/ical.php';
    exit;
}

if (preg_match('#^/villas/([0-9]+)/ical/import$#', $uri, $matches)) {
    $id = (int)$matches[1];
    if ($method === 'POST') {
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');
        $_POST['villa_id'] = $id;
        ExternalCalendar::create($_POST, $user['id']);
        Helpers::setFlash('success', 'Harici iCal takvimi eklendi ve senkronize edildi.');
    }
    Helpers::redirect("/villas/{$id}/ical");
}

if (preg_match('#^/villas/([0-9]+)/ical/sync-all$#', $uri, $matches)) {
    $id = (int)$matches[1];
    $calendars = ExternalCalendar::getByVilla($id);
    foreach ($calendars as $cal) {
        IcalEngine::syncExternalCalendar($cal);
    }
    Helpers::setFlash('success', 'Tüm iCal bağlantıları senkronize edildi.');
    Helpers::redirect("/villas/{$id}/ical");
}

if (preg_match('#^/villas/([0-9]+)/ical/delete/([0-9]+)$#', $uri, $matches)) {
    $villaId = (int)$matches[1];
    $calId = (int)$matches[2];
    ExternalCalendar::delete($calId, $user['id']);
    Helpers::setFlash('success', 'Takvim bağlantısı silindi.');
    Helpers::redirect("/villas/{$villaId}/ical");
}

// -------------------------------------------------------------
// PRIVATE CALENDAR JSON API ENDPOINTS
// -------------------------------------------------------------
if ($uri === '/api/dashboard/all-reservations') {
    $db = Database::getConnection();
    if (Auth::isAdmin()) {
        $stmt = $db->prepare("
            SELECT r.*, v.name as villa_name 
            FROM reservations r
            JOIN villas v ON r.villa_id = v.id
            WHERE v.status = 'active'
            ORDER BY r.start_date DESC, r.id DESC
        ");
        $stmt->execute();
    } else {
        $stmt = $db->prepare("
            SELECT r.*, v.name as villa_name 
            FROM reservations r
            JOIN villas v ON r.villa_id = v.id
            WHERE v.user_id = :user_id AND v.status = 'active'
            ORDER BY r.start_date DESC, r.id DESC
        ");
        $stmt->execute(['user_id' => $user['id']]);
    }
    Helpers::jsonResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}
if (preg_match('#^/api/villas/([0-9]+)/calendar$#', $uri, $matches)) {
    $villaId = (int)$matches[1];
    $villa = Villa::find($villaId, $user['id'], Auth::isAdmin());
    if (!$villa) Helpers::jsonResponse(['error' => 'Unauthorized'], 403);

    $start = $_GET['start'] ?? date('Y-m-01');
    $end = $_GET['end'] ?? date('Y-m-t');
    Helpers::jsonResponse(Reservation::getCalendarEvents($villaId, $start, $end));
}

if (preg_match('#^/api/villas/([0-9]+)/all-reservations$#', $uri, $matches)) {
    $villaId = (int)$matches[1];
    $villa = Villa::find($villaId, $user['id'], Auth::isAdmin());
    if (!$villa) Helpers::jsonResponse(['error' => 'Unauthorized'], 403);

    $year = $_GET['year'] ?? date('Y');
    Helpers::jsonResponse(Reservation::getAllReservations($villaId, $year));
}

if (preg_match('#^/api/villas/([0-9]+)/price-periods$#', $uri, $matches)) {
    $villaId = (int)$matches[1];
    $villa = Villa::find($villaId, $user['id'], Auth::isAdmin());
    if (!$villa) Helpers::jsonResponse(['error' => 'Unauthorized'], 403);

    Helpers::jsonResponse(PricePeriod::getByVilla($villaId));
}

if ($uri === '/api/occupancy-matrix') {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
    if ($month < 1 || $month > 12) $month = (int)date('m');
    if ($year < 2020 || $year > 2035) $year = (int)date('Y');

    if (Auth::isAdmin()) {
        $villas = Villa::allForAdmin(true);
    } else {
        $villas = Villa::allByUser($user['id'], true);
    }

    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate));

    $db = Database::getConnection();
    $reservations = [];
    if (!empty($villas)) {
        $villaIds = array_column($villas, 'id');
        $inClause = implode(',', array_map('intval', $villaIds));
        $stmt = $db->query("
            SELECT r.*, v.name as villa_name
            FROM reservations r
            JOIN villas v ON r.villa_id = v.id
            WHERE r.villa_id IN ({$inClause})
              AND r.start_date <= '{$endDate}'
              AND r.end_date >= '{$startDate}'
            ORDER BY r.start_date ASC
        ");
        $reservations = $stmt->fetchAll();
    }

    Helpers::jsonResponse([
        'year' => $year,
        'month' => $month,
        'days_in_month' => (int)date('t', strtotime($startDate)),
        'villas' => $villas,
        'reservations' => $reservations
    ]);
}

if (preg_match('#^/api/villas/([0-9]+)/reservations$#', $uri, $matches)) {
    $villaId = (int)$matches[1];
    $villa = Villa::find($villaId, $user['id'], Auth::isAdmin());
    if (!$villa) Helpers::jsonResponse(['error' => 'Unauthorized'], 403);

    $_POST['villa_id'] = $villaId;
    $res = Reservation::create($_POST, $user['id']);
    Helpers::jsonResponse($res);
}

if (preg_match('#^/api/reservations/([0-9]+)$#', $uri, $matches)) {
    $resId = (int)$matches[1];
    if ($method === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'POST') {
        $success = Reservation::delete($resId, $user['id']);
        Helpers::jsonResponse(['success' => $success]);
    }
}

// -------------------------------------------------------------
// ADMIN ROUTES
// -------------------------------------------------------------
if (str_starts_with($uri, '/admin')) {
    Auth::requireAdmin();

    if ($uri === '/admin/users' || $uri === '/admin') {
        $users = User::getAll();
        require __DIR__ . '/../views/admin/users.php';
        exit;
    }

    if ($uri === '/admin/users/create') {
        if ($method !== 'POST') Helpers::redirect('/admin/users');
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');

        $res = User::create($_POST);
        if ($res['success']) {
            Helpers::setFlash('success', $res['message']);
        } else {
            Helpers::setFlash('error', $res['message']);
        }
        Helpers::redirect('/admin/users');
    }

    if (preg_match('#^/admin/users/([0-9]+)/extend-license$#', $uri, $matches)) {
        if ($method !== 'POST') Helpers::redirect('/admin/users');
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');
        
        $userId = (int)$matches[1];
        $customDate = trim($_POST['custom_date'] ?? '');
        $days = (int)($_POST['days'] ?? 30);

        if (!empty($customDate)) {
            User::setLicenseDate($userId, $customDate);
            Helpers::setFlash('success', 'Kullanıcının lisans bitiş tarihi ' . date('d.m.Y', strtotime($customDate)) . ' olarak güncellendi.');
        } else {
            User::extendLicense($userId, $days);
            Helpers::setFlash('success', "Kullanıcıya +{$days} gün lisans süresi eklendi.");
        }

        Helpers::redirect('/admin/users');
    }

    if (preg_match('#^/admin/users/([0-9]+)/update$#', $uri, $matches)) {
        if ($method !== 'POST') Helpers::redirect('/admin/users');
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');

        $userId = (int)$matches[1];
        $res = User::updateUser($userId, $_POST);
        if ($res['success']) {
            Helpers::setFlash('success', $res['message']);
        } else {
            Helpers::setFlash('error', $res['message']);
        }
        Helpers::redirect('/admin/users');
    }

    if (preg_match('#^/admin/users/([0-9]+)/delete$#', $uri, $matches)) {
        if ($method !== 'POST') Helpers::redirect('/admin/users');
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');

        $userId = (int)$matches[1];
        $res = User::deleteUser($userId, Auth::id());
        if ($res['success']) {
            Helpers::setFlash('success', $res['message']);
        } else {
            Helpers::setFlash('error', $res['message']);
        }
        Helpers::redirect('/admin/users');
    }

    if (preg_match('#^/admin/users/([0-9]+)/toggle-status$#', $uri, $matches) || preg_match('#^/admin/users/toggle-status/([0-9]+)$#', $uri, $matches)) {
        if ($method !== 'POST') Helpers::redirect('/admin/users');
        if (!Helpers::verifyCsrf()) die('CSRF Mismatch');

        $userId = (int)$matches[1];
        User::toggleStatus($userId);
        Helpers::setFlash('success', 'Kullanıcı durumu güncellendi.');
        Helpers::redirect('/admin/users');
    }

    if ($uri === '/admin/villas') {
        $villas = Villa::allForAdmin();
        require __DIR__ . '/../views/admin/villas.php';
        exit;
    }

    if ($uri === '/admin/audit-logs') {
        $db = Database::getConnection();
        $logs = $db->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 100")->fetchAll();
        require __DIR__ . '/../views/admin/audit_logs.php';
        exit;
    }
}

// Fallback 404
http_response_code(404);
echo "<h1>404 - Sayfa Bulunamadı</h1>";
