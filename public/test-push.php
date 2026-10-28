<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

use App\WebPush;
use App\PushNotification;
use App\Database;
use App\Auth;

$db = Database::getConnection();
WebPush::initTable();

// Belirlenen kullanıcı
$defaultUser = $db->query("SELECT id, full_name, email FROM users ORDER BY id ASC LIMIT 1")->fetch();
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (Auth::id() ?: (int)($defaultUser['id'] ?? 3));

$stmt = $db->prepare("SELECT id, full_name, email FROM users WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();
if (!$user) {
    $user = $defaultUser;
    $userId = (int)($user['id'] ?? 3);
}

$ownerName = $user['full_name'] ?? 'Ev Sahibi';
$ownerEmail = $user['email'] ?? '';

// Kullanıcının kayıtlı WebPush bildirim abonelikleri
$subStmt = $db->prepare("SELECT id, endpoint, user_agent, created_at, updated_at FROM user_push_subscriptions WHERE user_id = :uid ORDER BY id DESC");
$subStmt->execute(['uid' => $userId]);
$subscriptions = $subStmt->fetchAll();

$osResult = null;
$wpResult = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_test') {
    $sampleDetails = [
        'id' => 999,
        'user_id' => $userId,
        'owner_name' => $ownerName,
        'villa_name' => 'Villa Sunset Luxe 18',
        'customer_name' => 'Ahmet Yılmaz',
        'customer_phone' => '0532 123 45 67',
        'start_date' => date('Y-m-d', strtotime('-5 days')),
        'end_date' => date('Y-m-d', strtotime('+1 day')),
        'checkout_time' => '10:00',
        'remaining_payment_tr' => '12.500 ₺'
    ];

    $osResult = PushNotification::sendCheckoutReminder($sampleDetails);
    $wpResult = WebPush::sendCheckoutReminder($sampleDetails);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📱 TakvimTakip Çoklu Push Test Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen p-4 sm:p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-indigo-900 text-white p-6">
            <div class="flex items-center space-x-3">
                <span class="w-10 h-10 rounded-xl bg-indigo-700/80 flex items-center justify-center text-amber-300 text-xl">
                    <i class="fa-solid fa-bell"></i>
                </span>
                <div>
                    <h1 class="text-xl font-bold">Çoklu Push Bildirim Test Paneli</h1>
                    <p class="text-indigo-200 text-xs mt-0.5">Google Play (Android) & Apple App Store (iOS) OneSignal + Web Push Hibrit Altyapı</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Target User Info -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Hedef Ev Sahibi / Kullanıcı</span>
                    <strong class="text-base text-slate-900"><?= htmlspecialchars($ownerName) ?></strong>
                    <span class="text-xs text-slate-500 block">ID: #<?= $userId ?> (OneSignal external_id: "<?= $userId ?>") &bull; <?= htmlspecialchars($ownerEmail) ?></span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                        <i class="fa-solid fa-mobile-screen-button mr-1.5"></i>
                        OneSignal Aktif
                    </span>
                </div>
            </div>

            <!-- Send Test Push Form -->
            <form method="POST" class="pt-2">
                <input type="hidden" name="action" value="send_test">
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition active:scale-98 flex items-center justify-center space-x-2 cursor-pointer">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Tüm Cihazlara Örnek Çıkış Bildirimi Fırlat</span>
                </button>
            </form>

            <!-- Test Result Box -->
            <?php if ($osResult !== null || $wpResult !== null): ?>
                <div class="pt-2 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Gönderim Raporu</h3>
                    
                    <!-- OneSignal Native Result -->
                    <div class="p-4 rounded-xl border <?= ($osResult['success'] ?? false) ? 'bg-indigo-50 border-indigo-200 text-indigo-950' : 'bg-rose-50 border-rose-200 text-rose-950' ?>">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-xs uppercase flex items-center">
                                <i class="fa-brands fa-android text-emerald-600 mr-1.5 text-sm"></i>
                                <i class="fa-brands fa-apple text-slate-700 mr-2 text-sm"></i>
                                OneSignal Native Mobil (Play Store & App Store)
                            </span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded <?= ($osResult['success'] ?? false) ? 'bg-indigo-200 text-indigo-900' : 'bg-rose-200 text-rose-900' ?>">
                                HTTP <?= $osResult['http_code'] ?? '0' ?>
                            </span>
                        </div>
                        <pre class="bg-white/80 p-2.5 rounded text-[11px] font-mono overflow-auto max-h-32 border border-indigo-100 mt-2"><?= htmlspecialchars(json_encode($osResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                    </div>

                    <!-- WebPush Result -->
                    <div class="p-4 rounded-xl border <?= ($wpResult['success'] ?? false) ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : 'bg-slate-50 border-slate-200 text-slate-800' ?>">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-xs uppercase flex items-center">
                                <i class="fa-solid fa-globe text-indigo-600 mr-2 text-sm"></i>
                                Web Push (Masaüstü & PWA Tarayıcılar)
                            </span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded <?= ($wpResult['success'] ?? false) ? 'bg-emerald-200 text-emerald-900' : 'bg-slate-200 text-slate-700' ?>">
                                <?= ($wpResult['sent_count'] ?? 0) ?> Cihaz
                            </span>
                        </div>
                        <pre class="bg-white/80 p-2.5 rounded text-[11px] font-mono overflow-auto max-h-32 border border-slate-200 mt-2"><?= htmlspecialchars(json_encode($wpResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Keys Info -->
            <div class="border-t border-slate-100 pt-4 text-xs text-slate-400 space-y-1">
                <div><b>OneSignal App ID:</b> <code class="text-slate-600 font-mono text-[10px]"><?= ONESIGNAL_APP_ID ?></code></div>
                <div><b>Hedef Eşleşmesi:</b> Ev sahibi giriş yaptığında OneSignal <code>external_id = "<?= $userId ?>"</code> olarak etiketlenir.</div>
            </div>
        </div>
    </div>
</body>
</html>
