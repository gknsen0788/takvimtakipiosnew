<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

use App\WebPush;
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

// Kullanıcının kayıtlı bildirim abonelikleri
$subStmt = $db->prepare("SELECT id, endpoint, user_agent, created_at, updated_at FROM user_push_subscriptions WHERE user_id = :uid ORDER BY id DESC");
$subStmt->execute(['uid' => $userId]);
$subscriptions = $subStmt->fetchAll();

$result = null;
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

    $result = WebPush::sendCheckoutReminder($sampleDetails);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📱 TakvimTakip Web Push Test Modülü</title>
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
                    <h1 class="text-xl font-bold">Yerel Web Push Bildirim Test Paneli</h1>
                    <p class="text-indigo-200 text-xs mt-0.5">Harici servissiz (RFC 8291 aes128gcm & RFC 8292 VAPID) doğrudan tarayıcı/mobil bildirimleri</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Target User Info -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Hedef Ev Sahibi / Kullanıcı</span>
                    <strong class="text-base text-slate-900"><?= htmlspecialchars($ownerName) ?></strong>
                    <span class="text-xs text-slate-500 block">ID: #<?= $userId ?> &bull; <?= htmlspecialchars($ownerEmail) ?></span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?= count($subscriptions) > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                        <i class="fa-solid <?= count($subscriptions) > 0 ? 'fa-mobile-screen-button' : 'fa-triangle-exclamation' ?> mr-1.5"></i>
                        <?= count($subscriptions) ?> Kayıtlı Cihaz
                    </span>
                </div>
            </div>

            <!-- Subscriptions List -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Kullanıcının Kayıtlı Push Cihazları</h3>
                <?php if (empty($subscriptions)): ?>
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-amber-900 text-xs space-y-2">
                        <div class="flex items-center space-x-2 font-bold text-sm text-amber-950">
                            <i class="fa-solid fa-circle-exclamation text-amber-600"></i>
                            <span>Bu kullanıcıya ait henüz kayıtlı bildirim cihazı bulunmuyor!</span>
                        </div>
                        <p>Kullanıcının telefonundan veya bilgisayarından uygulamaya giriş yapıp çıkan bildirim kutucuğundan <b>"Bildirimleri Aç"</b> butonuna basması gerekmektedir.</p>
                        <div class="pt-2">
                            <button id="btnSubscribeThisDevice" type="button" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-2 rounded-lg text-xs transition inline-flex items-center space-x-1.5 cursor-pointer">
                                <i class="fa-solid fa-plus"></i>
                                <span>Bu Tarayıcıyı #<?= $userId ?> Kullanıcısına Abone Yap (Test İçin)</span>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($subscriptions as $idx => $sub): ?>
                            <div class="bg-white p-3 rounded-lg border border-slate-200 text-xs flex justify-between items-center shadow-xs">
                                <div class="truncate mr-3">
                                    <div class="font-semibold text-slate-800 flex items-center space-x-2">
                                        <i class="fa-solid fa-desktop text-slate-400"></i>
                                        <span class="truncate"><?= htmlspecialchars(substr($sub['user_agent'] ?: 'Web Browser', 0, 50)) ?>...</span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 block truncate"><?= htmlspecialchars(substr($sub['endpoint'], 0, 60)) ?>...</span>
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0"><?= $sub['created_at'] ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="pt-1">
                            <button id="btnSubscribeThisDevice" type="button" class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold inline-flex items-center space-x-1 cursor-pointer">
                                <i class="fa-solid fa-plus-circle"></i>
                                <span>Mevcut bu tarayıcıyı da ekle/yenile</span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Send Test Push Form -->
            <form method="POST" class="pt-2">
                <input type="hidden" name="action" value="send_test">
                <button type="submit" <?= empty($subscriptions) ? 'disabled' : '' ?> class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 disabled:opacity-50 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition active:scale-98 flex items-center justify-center space-x-2 cursor-pointer">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Örnek Çıkış Hatırlatma Push Bildirimi Gönder</span>
                </button>
            </form>

            <!-- Test Result Box -->
            <?php if ($result !== null): ?>
                <div class="pt-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Gönderim Sonucu</h3>
                    <?php if ($result['success']): ?>
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 p-4 rounded-xl space-y-2">
                            <div class="flex items-center space-x-2 font-bold text-emerald-950">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                                <span>✅ Bildirim Başarıyla İletildi! (<?= $result['sent_count'] ?> Cihaz)</span>
                            </div>
                            <p class="text-xs text-emerald-800">Hedef kullanıcının kayıtlı cihazlarına doğrudan RFC 8291 aes128gcm ile şifrelenip iletildi.</p>
                            <pre class="bg-white/80 p-3 rounded-lg text-[11px] text-slate-700 font-mono overflow-auto max-h-40 border border-emerald-100"><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        </div>
                    <?php else: ?>
                        <div class="bg-rose-50 border border-rose-200 text-rose-900 p-4 rounded-xl space-y-2">
                            <div class="flex items-center space-x-2 font-bold text-rose-950">
                                <i class="fa-solid fa-circle-xmark text-rose-600 text-base"></i>
                                <span>❌ Bildirim Gönderilemedi!</span>
                            </div>
                            <p class="text-xs text-rose-800"><?= htmlspecialchars($result['error'] ?? 'Hata oluştu') ?></p>
                            <?php if (!empty($result['details'])): ?>
                                <pre class="bg-white/80 p-3 rounded-lg text-[11px] text-slate-700 font-mono overflow-auto max-h-40 border border-rose-100"><?= htmlspecialchars(json_encode($result['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Public Key Info -->
            <div class="border-t border-slate-100 pt-4 text-xs text-slate-400 space-y-1">
                <div><b>VAPID Public Key:</b> <code class="text-slate-600 select-all font-mono text-[10px] break-all"><?= \App\WebPush::getPublicKey() ?></code></div>
                <div><b>Protokol:</b> RFC 8291 (aes128gcm) &bull; RFC 8292 (VAPID)</div>
            </div>
        </div>
    </div>

    <!-- Push Subscribe Script for Testing on this Page -->
    <script>
        const VAPID_KEY = "<?= \App\WebPush::getPublicKey() ?>";
        const TARGET_USER_ID = <?= $userId ?>;

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        document.getElementById('btnSubscribeThisDevice')?.addEventListener('click', async function() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                alert('Tarayıcınız Web Push desteklemiyor.');
                return;
            }

            try {
                let reg = await navigator.serviceWorker.getRegistration('/sw.js');
                if (!reg) {
                    reg = await navigator.serviceWorker.register('/sw.js');
                }
                reg = await navigator.serviceWorker.ready;

                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    alert('Bildirim izni verilmedi: ' + permission);
                    return;
                }

                let sub = await reg.pushManager.getSubscription();
                if (!sub) {
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(VAPID_KEY)
                    });
                }

                const json = sub.toJSON();
                const res = await fetch('/api/push-subscribe', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify(json)
                });
                const data = await res.json();
                if (data.success) {
                    alert('Bu tarayıcı başarıyla kaydedildi! Sayfa yenileniyor...');
                    window.location.reload();
                } else {
                    alert('Hata: ' + (data.error || 'Abonelik kaydedilemedi'));
                }
            } catch (e) {
                alert('Abonelik hatası: ' + e.message);
            }
        });
    </script>
</body>
</html>
