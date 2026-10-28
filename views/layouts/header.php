<?php
use App\Auth;
use App\Helpers;
?>
<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-50 overflow-x-hidden max-w-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="color-scheme" content="light">
    <title><?= Helpers::sanitize($title ?? 'TakvimTakip - Müsaitlik & Takvim Yönetimi') ?></title>
    
    <!-- PWA & Mobile Web App Meta Tags -->
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json?v=7">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= APP_URL ?>/css/icon-192.png?v=7">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= APP_URL ?>/css/apple-touch-icon.png?v=7">
    <meta name="theme-color" content="#000000">
    <meta name="theme-color" content="#000000" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#000000" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="TakvimTakip">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function(err) {
                    console.warn('ServiceWorker kayıt hatası:', err);
                });
            });
        }
        // Force Android Navigation Bar Theme Color to Black (#000000)
        (function() {
            function enforceBlackTheme() {
                let metas = document.querySelectorAll('meta[name="theme-color"]');
                metas.forEach(m => m.setAttribute('content', '#000000'));
            }
            enforceBlackTheme();
            document.addEventListener('DOMContentLoaded', enforceBlackTheme);
        })();
    </script>

    <!-- Native Web Push Notifications (RFC 8291 / RFC 8292 VAPID) -->
    <?php if (Auth::check()): ?>
    <script>
        window.VAPID_PUBLIC_KEY = "<?= \App\WebPush::getPublicKey() ?>";

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

        async function initWebPush() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                return;
            }

            try {
                let reg = await navigator.serviceWorker.getRegistration('/sw.js');
                if (!reg) {
                    reg = await navigator.serviceWorker.register('/sw.js');
                }
                reg = await navigator.serviceWorker.ready;
                
                // Eğer daha önceden izin verilmişse sessizce abone yap veya doğrula
                if (Notification.permission === 'granted') {
                    let sub = await reg.pushManager.getSubscription();
                    if (!sub) {
                        sub = await reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(window.VAPID_PUBLIC_KEY)
                        });
                    }
                    if (sub) {
                        await sendSubscriptionToServer(sub);
                    }
                } else if (Notification.permission === 'default') {
                    showPushBanner();
                }
            } catch (err) {
                console.warn('Web Push Başlatma Hatası:', err);
            }
        }

        async function sendSubscriptionToServer(subscription) {
            try {
                const json = subscription.toJSON();
                await fetch('/api/push-subscribe', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify(json)
                });
            } catch (e) {
                console.warn('Abonelik sunucuya iletilemedi:', e);
            }
        }

        window.requestPushPermission = async function() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                alert('Tarayıcınız veya cihazınız Web Push bildirimlerini desteklemiyor.');
                return false;
            }

            try {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    hidePushBanner();
                    let reg = await navigator.serviceWorker.getRegistration('/sw.js');
                    if (!reg) {
                        reg = await navigator.serviceWorker.register('/sw.js');
                    }
                    reg = await navigator.serviceWorker.ready;
                    let sub = await reg.pushManager.getSubscription();
                    if (!sub) {
                        sub = await reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(window.VAPID_PUBLIC_KEY)
                        });
                    }
                    if (sub) {
                        await sendSubscriptionToServer(sub);
                    }
                    if (typeof showToast === 'function') {
                        showToast('Bildirimler başarıyla açıldı! Çıkış hatırlatmalarını artık telefonunuzdan alacaksınız.', 'success');
                    } else {
                        alert('Bildirimler başarıyla açıldı! Yarınki çıkış hatırlatmaları telefonunuza iletilecektir.');
                    }
                    return true;
                } else if (permission === 'denied') {
                    hidePushBanner();
                    alert('Bildirim izinleri engellenmiş. Tarayıcınızın site ayarlarından bildirimlere izin verebilirsiniz.');
                    return false;
                }
            } catch (e) {
                console.error('Bildirim izin hatası:', e);
            }
            return false;
        };

        function showPushBanner() {
            const dismissedAt = localStorage.getItem('push_banner_dismissed_at');
            if (dismissedAt && (Date.now() - parseInt(dismissedAt, 10)) < 3 * 86400 * 1000) {
                return;
            }
            const banner = document.getElementById('pushPermissionBanner');
            if (banner) {
                banner.classList.remove('hidden');
            }
        }

        window.dismissPushBanner = function() {
            localStorage.setItem('push_banner_dismissed_at', Date.now().toString());
            hidePushBanner();
        };

        function hidePushBanner() {
            const banner = document.getElementById('pushPermissionBanner');
            if (banner) {
                banner.classList.add('hidden');
            }
        }

        window.addEventListener('load', function() {
            initWebPush();
        });
    </script>
    <?php endif; ?>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome / Lucide Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- FullCalendar v6 JS/CSS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/locales/tr.global.min.js"></script>
    <!-- Custom App CSS with Cache Buster -->
    <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css?v=<?= time() ?>">
    <style>
        :root {
            color-scheme: light !important;
        }

        /* Document Root Isolation & Full-Height Setup for Mobile & WebViews */
        html, body {
            max-width: 100vw !important;
            width: 100% !important;
            color-scheme: light !important;
        }

        html {
            height: 100%;
        }

        body {
            min-height: 100%;
            min-height: 100vh;
        }

        #ganttRightPane, .overflow-auto, .overflow-x-auto {
            touch-action: auto !important;
        }

        /* iOS Safari & Dark Mode Override: Ensure all input controls stay light */
        input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="reset"]),
        select,
        textarea {
            background-color: #ffffff;
            color: #1e293b;
            -webkit-text-fill-color: #1e293b;
            color-scheme: light !important;
        }

        input::placeholder,
        textarea::placeholder {
            color: #94a3b8;
            -webkit-text-fill-color: #94a3b8;
            opacity: 1;
        }

        /* iOS Safari Form Inputs & Grid Overflow Fix */
        input[type="date"], select, input[type="text"], input[type="tel"], input[type="email"], input[type="password"] {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            box-sizing: border-box !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }

        input[type="date"] {
            min-height: 2.375rem !important;
            line-height: normal !important;
        }

        input[type="date"]::-webkit-date-and-time-value {
            text-align: left !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: normal !important;
            min-height: 1.2em !important;
        }

        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.5rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.1em 1.1em !important;
            padding-right: 1.75rem !important;
        }

        /* Mini Icon-Only Sidebar Mode (Daraltılmış Sol Menü) */
        #desktopSidebar.sidebar-collapsed-mini {
            width: 4.5rem !important; /* 72px */
        }
        #desktopSidebar.sidebar-collapsed-mini .sidebar-text,
        #desktopSidebar.sidebar-collapsed-mini .sidebar-header-title,
        #desktopSidebar.sidebar-collapsed-mini .sidebar-section-title,
        #desktopSidebar.sidebar-collapsed-mini .sidebar-user-info {
            display: none !important;
        }
        #desktopSidebar.sidebar-collapsed-mini .sidebar-nav-item {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        #desktopSidebar.sidebar-collapsed-mini .sidebar-header-box {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* Modern Soft & Sleek Calendar UI Architecture */
        .fc-theme-standard .fc-scrollgrid {
            border: none !important;
            background: transparent !important;
        }

        .fc td,
        .fc th,
        .fc .fc-theme-standard td,
        .fc .fc-theme-standard th,
        .fc .fc-scrollgrid-section td,
        .fc .fc-scrollgrid-section th,
        .fc .fc-daygrid-day {
            border: none !important;
            background-color: transparent !important;
            padding: 3px !important;
            box-sizing: border-box !important;
        }

        /* Soft Rounded Floating Day Cards */
        .fc .fc-daygrid-day:not(.fc-day-other) .fc-daygrid-day-frame {
            background-color: #ffffff;
            aspect-ratio: 1 / 1 !important;
            position: relative !important;
            overflow: hidden !important;
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
            transition: none !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }

        /* Non-Current Month / Disabled Day / Empty Box Styling (Hidden) */
        .fc .fc-day-other,
        .fc .fc-day-other .fc-daygrid-day-frame,
        .fc .fc-daygrid-day.fc-day-other .fc-daygrid-day-frame,
        .fc-theme-standard td.fc-day-other .fc-daygrid-day-frame {
            background-color: transparent !important;
            background: transparent !important;
            border-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* Disable month transition animations */
        .fc-view-harness,
        .fc-view-harness-active,
        .fc-daygrid-body,
        .fc-scrollgrid-sync-table {
            transition: none !important;
            animation: none !important;
        }

        /* Hover elevation and pointer cursor on available cells */
        .fc .fc-daygrid-day:not(.fc-day-other),
        .fc .fc-daygrid-day:not(.fc-day-other) * {
            cursor: pointer !important;
        }

        .fc .fc-day-other,
        .fc .fc-day-other * {
            cursor: default !important;
        }

        .fc .fc-daygrid-day:not(.fc-day-other):hover .fc-daygrid-day-frame {
            transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
            border-color: #cbd5e1 !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08) !important;
        }

        /* Header (Day Names) */
        .fc .fc-col-header-cell-cushion {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            letter-spacing: 0.08em !important;
            padding: 8px 0 !important;
            text-decoration: none !important;
        }

        /* Date Numbers Centered */
        .fc .fc-daygrid-day-top {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: absolute !important;
            inset: 0 !important;
            pointer-events: auto !important;
        }

        .fc .fc-daygrid-day-number {
            font-size: 0.9rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            padding: 0 !important;
            margin: 0 !important;
            line-height: 1 !important;
            z-index: 10 !important;
            pointer-events: auto !important;
        }

        /* Empty / Non-Current Month Cells (Completely Hidden) */
        .fc .fc-day-other,
        .fc .fc-day-other *,
        .fc .fc-day-other .fc-daygrid-day-frame,
        .fc .fc-daygrid-day.fc-day-other .fc-daygrid-day-frame,
        .fc-theme-standard td.fc-day-other .fc-daygrid-day-frame {
            background-color: transparent !important;
            background: transparent !important;
            border-color: transparent !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* Hide numbers and content for next/prev month days */
        .fc .fc-day-other .fc-daygrid-day-top,
        .fc .fc-day-other .fc-daygrid-day-number,
        .fc .fc-day-other .fc-daygrid-day-events,
        .fc .fc-day-other .cell-price-tag {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

        /* Hide FullCalendar background event overlay layer */
        .fc-daygrid-day-bg,
        .fc-bg-event {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

        /* Reserved Date Styles (#fd5a5f) */
        .fc-has-reservation .fc-daygrid-day-frame,
        .fc-daygrid-day.fc-has-reservation .fc-daygrid-day-frame {
            background-color: #fd5a5f !important;
            background: #fd5a5f !important;
            border-color: transparent !important;
            box-shadow: 0 2px 8px rgba(253, 90, 95, 0.25) !important;
        }

        /* Desktop & Mobile Selection Highlight Overlay (#fd5a5f) */
        .fc .fc-highlight,
        .fc-pending-start-cell,
        .fc-pending-start-cell .fc-daygrid-day-frame,
        .fc-selected-range-cell,
        .fc-selected-range-cell .fc-daygrid-day-frame {
            background-color: #fd5a5f !important;
            background: #fd5a5f !important;
            border-color: transparent !important;
            opacity: 1 !important;
        }

        .fc .fc-has-reservation .fc-daygrid-day-number,
        .fc .fc-has-reservation a,
        .fc .fc-has-reservation a.fc-daygrid-day-number,
        .fc-daygrid-day.fc-has-reservation .fc-daygrid-day-number,
        .fc-daygrid-day.fc-has-reservation a,
        .fc-pending-start-cell .fc-daygrid-day-number,
        .fc-pending-start-cell a,
        .fc-selected-range-cell .fc-daygrid-day-number,
        .fc-selected-range-cell a {
            color: #ffffff !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
            font-weight: 800 !important;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col overflow-x-hidden max-w-full w-full relative">
    
<?php if (Auth::check() && empty($hideNav)): ?>
<div id="appLayoutWrapper" class="flex-1 flex flex-col lg:flex-row w-full min-w-0 relative">
<?php
$displayPlan = 'BASIC PLAN';
$userObj = Auth::user();
if ($userObj) {
    if ($userObj['role'] === 'admin') {
        $displayPlan = 'PRO PLAN';
    } else {
        $db = \App\Database::getConnection();
        $stmt = $db->prepare("SELECT license_expires_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userObj['id']]);
        $expDate = $stmt->fetchColumn();

        if ($expDate) {
            $today = date('Y-m-d');
            $remDays = (int)floor((strtotime($expDate) - strtotime($today)) / 86400);
            if ($remDays >= 11) {
                $displayPlan = 'PRO PLAN';
            } else {
                $displayPlan = 'BASIC PLAN';
            }
        }
    }
}

$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isDashboard = str_contains($currentUri, '/dashboard');
$isCalendarMgmt = str_contains($currentUri, '/calendar-management') || (str_contains($currentUri, '/villas/') && str_contains($currentUri, '/calendar'));
$isOccupancyCal = str_contains($currentUri, '/occupancy-calendar');
$isVillas = str_contains($currentUri, '/villas') && !$isCalendarMgmt && !$isOccupancyCal;
$isProfile = str_contains($currentUri, '/profile');
$isAdminUsers = str_contains($currentUri, '/admin');
?>

<!-- ========================================================= -->
<!-- 1. MOBILE TOP HEADER BAR (Visible on screens < 1024px) -->
<!-- ========================================================= -->
<nav class="lg:hidden bg-[#4338ca] text-white shadow-md relative w-full shrink-0">
    <div class="px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
            <!-- Left Logo -->
            <div class="flex items-center shrink-0">
                <a href="<?= APP_URL ?>/dashboard" class="flex items-center space-x-2 font-bold text-xl tracking-tight">
                    <img src="<?= APP_URL ?>/uploads/logo.png" alt="TakvimTakip" class="h-9 w-auto object-contain max-h-10" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" />
                    <div class="flex items-center space-x-2" style="display:none;">
                        <i class="fa-solid fa-calendar-check text-amber-300"></i>
                        <span>TakvimTakip</span>
                    </div>
                </a>
            </div>

            <!-- Mobile Hamburger Button -->
            <div class="flex items-center space-x-2">
                <button id="mobileMenuBtn" type="button" class="p-2 rounded-xl text-white hover:bg-indigo-700/80 focus:outline-none transition active:scale-95" aria-label="Menüyü Aç">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Backdrop Overlay (< 1024px) -->
<div id="mobileMenuBackdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 hidden transition-opacity duration-300 opacity-0 lg:hidden" aria-hidden="true"></div>

<!-- Mobile Offcanvas Drawer (< 1024px) -->
<div id="mobileMenuDrawer" class="fixed inset-y-0 left-0 w-72 max-w-[80vw] bg-white text-slate-800 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out shadow-2xl flex flex-col justify-between lg:hidden border-r border-slate-200">
    <div>
        <!-- Drawer Header -->
        <div class="h-16 px-4 border-b border-indigo-600/60 flex items-center justify-between bg-[#4338ca]">
            <a href="<?= APP_URL ?>/dashboard" class="flex items-center space-x-2 font-bold text-lg tracking-tight text-white">
                <img src="<?= APP_URL ?>/uploads/logo.png" alt="TakvimTakip" class="h-9 w-auto object-contain max-h-10" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" />
                <div class="flex items-center space-x-2" style="display:none;">
                    <i class="fa-solid fa-calendar-check text-amber-300"></i>
                    <span>TakvimTakip</span>
                </div>
            </a>
            <button id="mobileMenuCloseBtn" type="button" class="w-8 h-8 rounded-full bg-indigo-700/80 hover:bg-indigo-600 text-indigo-100 hover:text-white flex items-center justify-center text-sm font-bold transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Drawer Links -->
        <nav class="p-2 space-y-1.5">
            <div class="px-3 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Menü</div>

            <a href="<?= APP_URL ?>/dashboard" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isDashboard ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>">
                <i class="fa-solid fa-layer-group text-indigo-600 w-5 text-center text-base shrink-0"></i>
                <span class="truncate">Anasayfa</span>
            </a>

            <a href="<?= APP_URL ?>/villas" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isVillas ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>">
                <i class="fa-solid fa-house-chimney text-indigo-600 w-5 text-center text-base shrink-0"></i>
                <span class="truncate">Konutlar</span>
            </a>

            <a href="<?= APP_URL ?>/calendar-management" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isCalendarMgmt ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>">
                <i class="fa-solid fa-calendar-days text-indigo-600 w-5 text-center text-base shrink-0"></i>
                <span class="truncate">Takvim Yönetimi</span>
            </a>

            <a href="<?= APP_URL ?>/occupancy-calendar" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isOccupancyCal ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>">
                <i class="fa-solid fa-calendar-check text-indigo-600 w-5 text-center text-base shrink-0"></i>
                <span class="truncate">Doluluk Takvimi</span>
            </a>

            <?php if (!Auth::isAdmin()): ?>
                <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Hesap</div>
                <a href="<?= APP_URL ?>/profile" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isProfile ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>">
                    <i class="fa-solid fa-user-gear text-indigo-600 w-5 text-center text-base shrink-0"></i>
                    <span class="truncate">Hesap Bilgilerim</span>
                </a>
            <?php endif; ?>

            <?php if (Auth::isAdmin()): ?>
                <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Yönetim</div>
                <a href="<?= APP_URL ?>/admin/users" class="flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isAdminUsers ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>">
                    <i class="fa-solid fa-user-shield text-indigo-600 w-5 text-center text-base shrink-0"></i>
                    <span class="truncate">Admin Paneli</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Drawer Footer -->
    <div class="p-3 border-t border-slate-200/90 bg-slate-50/80 space-y-2">
        <div class="flex items-center justify-between">
            <div class="truncate">
                <div class="font-bold text-slate-800 text-xs truncate"><?= Helpers::sanitize(Auth::user()['full_name']) ?></div>
                <div class="text-indigo-600 text-[9px] uppercase font-extrabold tracking-wider mt-0.5"><?= $displayPlan ?></div>
            </div>
        </div>
        <a href="<?= APP_URL ?>/logout" class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 shadow-xs active:scale-95" title="Çıkış Yap">
            <i class="fa-solid fa-right-from-bracket text-sm shrink-0"></i>
            <span class="truncate">Çıkış Yap</span>
        </a>
    </div>
</div>


<!-- ========================================================= -->
<!-- 2. DESKTOP PERMANENT LEFT SIDEBAR (Visible on screens >= 1024px) -->
<!-- ========================================================= -->
<aside id="desktopSidebar" class="hidden lg:flex w-64 shrink-0 bg-white border-r border-slate-200/90 flex-col justify-between h-screen sticky top-0 z-30 shadow-xs select-none transition-all duration-300 ease-in-out overflow-x-hidden">
    <div>
        <!-- Sidebar Top Header / Logo + Hamburger Button -->
        <div class="h-16 px-3 border-b border-indigo-600/60 flex items-center justify-between bg-[#4338ca] shrink-0 sidebar-header-box">
            <a href="<?= APP_URL ?>/dashboard" class="flex items-center space-x-2 font-bold text-lg tracking-tight text-white truncate sidebar-header-title">
                <img src="<?= APP_URL ?>/uploads/logo.png" alt="TakvimTakip" class="h-8 w-auto object-contain max-h-9" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" />
                <div class="flex items-center space-x-2" style="display:none;">
                    <i class="fa-solid fa-calendar-check text-amber-300"></i>
                    <span class="sidebar-text">TakvimTakip</span>
                </div>
            </a>
            <!-- Hamburger Button (Always visible on left sidebar!) -->
            <button id="sidebarToggleBtn" type="button" class="w-9 h-9 rounded-xl bg-indigo-700/80 hover:bg-indigo-600 text-indigo-100 hover:text-white flex items-center justify-center text-base font-bold transition shrink-0 cursor-pointer shadow-xs active:scale-95" title="Menüyü Daralt / Genişlet">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="p-2 space-y-1.5">
            <div class="px-3 pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 sidebar-section-title">Menü</div>

            <a href="<?= APP_URL ?>/dashboard" class="sidebar-nav-item flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isDashboard ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>" title="Anasayfa">
                <i class="fa-solid fa-layer-group text-indigo-600 w-5 text-center text-base shrink-0"></i>
                <span class="sidebar-text truncate">Anasayfa</span>
            </a>

            <a href="<?= APP_URL ?>/villas" class="sidebar-nav-item flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isVillas ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>" title="Konutlar">
                <i class="fa-solid fa-house-chimney text-indigo-600 w-5 text-center text-base shrink-0"></i>
                <span class="sidebar-text truncate">Konutlar</span>
            </a>

            <a href="<?= APP_URL ?>/calendar-management" class="sidebar-nav-item flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isCalendarMgmt ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>" title="Takvim Yönetimi">
                <i class="fa-solid fa-calendar-days text-indigo-600 w-5 text-center text-base shrink-0"></i>
                <span class="sidebar-text truncate">Takvim Yönetimi</span>
            </a>

            <a href="<?= APP_URL ?>/occupancy-calendar" class="sidebar-nav-item flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isOccupancyCal ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>" title="Doluluk Takvimi">
                <i class="fa-solid fa-calendar-check text-indigo-600 w-5 text-center text-base shrink-0"></i>
                <span class="sidebar-text truncate">Doluluk Takvimi</span>
            </a>

            <?php if (!Auth::isAdmin()): ?>
                <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 sidebar-section-title">Hesap</div>
                <a href="<?= APP_URL ?>/profile" class="sidebar-nav-item flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isProfile ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>" title="Hesap Bilgilerim">
                    <i class="fa-solid fa-user-gear text-indigo-600 w-5 text-center text-base shrink-0"></i>
                    <span class="sidebar-text truncate">Hesap Bilgilerim</span>
                </a>
            <?php endif; ?>

            <?php if (Auth::isAdmin()): ?>
                <div class="px-3 pt-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 sidebar-section-title">Yönetim</div>
                <a href="<?= APP_URL ?>/admin/users" class="sidebar-nav-item flex items-center space-x-3 px-3.5 py-3 rounded-xl font-bold text-xs transition <?= $isAdminUsers ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-semibold' ?>" title="Admin Paneli">
                    <i class="fa-solid fa-user-shield text-indigo-600 w-5 text-center text-base shrink-0"></i>
                    <span class="sidebar-text truncate">Admin Paneli</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Sidebar Bottom User Card & Logout -->
    <div class="p-3 border-t border-slate-200/90 bg-slate-50/80 space-y-2">
        <div class="flex items-center justify-between sidebar-user-info">
            <div class="truncate">
                <div class="font-bold text-slate-800 text-xs truncate"><?= Helpers::sanitize(Auth::user()['full_name']) ?></div>
                <div class="text-indigo-600 text-[9px] uppercase font-extrabold tracking-wider mt-0.5"><?= $displayPlan ?></div>
            </div>
        </div>
        <a href="<?= APP_URL ?>/logout" class="sidebar-nav-item w-full bg-rose-600 hover:bg-rose-700 text-white py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 shadow-xs active:scale-95" title="Çıkış Yap">
            <i class="fa-solid fa-right-from-bracket text-sm shrink-0"></i>
            <span class="sidebar-text truncate">Çıkış Yap</span>
        </a>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('desktopSidebar');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const openBtn = document.getElementById('mobileMenuBtn');
    const closeBtn = document.getElementById('mobileMenuCloseBtn');
    const drawer = document.getElementById('mobileMenuDrawer');
    const backdrop = document.getElementById('mobileMenuBackdrop');

    // Restore mini collapsed state from localStorage
    const isMini = localStorage.getItem('sidebar_mini') === 'true';
    if (isMini && sidebar) {
        collapseSidebarMini(false);
    }

    function collapseSidebarMini(animate = true) {
        if (!sidebar) return;
        if (!animate) sidebar.classList.add('duration-0');
        sidebar.classList.remove('w-64');
        sidebar.classList.add('sidebar-collapsed-mini');
        if (!animate) setTimeout(() => sidebar.classList.remove('duration-0'), 50);
        localStorage.setItem('sidebar_mini', 'true');
    }

    function expandSidebarMini() {
        if (!sidebar) return;
        sidebar.classList.remove('sidebar-collapsed-mini');
        sidebar.classList.add('w-64');
        localStorage.setItem('sidebar_mini', 'false');
    }

    function toggleSidebarMini() {
        if (!sidebar) return;
        if (sidebar.classList.contains('sidebar-collapsed-mini')) {
            expandSidebarMini();
        } else {
            collapseSidebarMini();
        }
    }

    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebarMini);

    // Mobile Offcanvas Drawer
    function openMobileDrawer() {
        if (!drawer || !backdrop) return;
        backdrop.classList.remove('hidden');
        setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
        drawer.classList.remove('-translate-x-full');
        drawer.classList.add('translate-x-0');
        document.body.classList.add('overflow-hidden');
    }

    function closeMobileDrawer() {
        if (!drawer || !backdrop) return;
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('-translate-x-full');
        backdrop.classList.add('opacity-0');
        setTimeout(() => {
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }, 300);
    }

    if (openBtn) openBtn.addEventListener('click', openMobileDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeMobileDrawer);
    if (backdrop) backdrop.addEventListener('click', closeMobileDrawer);

    // Prevent root document horizontal scrolling on iOS Safari / Mobile
    window.addEventListener('scroll', function() {
        if (window.scrollX !== 0) {
            window.scrollTo(0, window.scrollY);
        }
    }, { passive: true });
});
</script>
<?php endif; ?>

<!-- Main Content Area Wrapper -->
<?php if (!empty($hideNav)): ?>
<div id="mainContentWrapper" class="flex-1 flex flex-col justify-center items-center min-w-0 w-full transition-all duration-300 ease-in-out" style="min-height: calc(100vh - 65px);">
    <main class="w-full max-w-md mx-auto px-4 py-4 sm:py-8 flex flex-col justify-center items-center flex-1">
<?php else: ?>
<div id="mainContentWrapper" class="flex-1 flex flex-col min-w-0 w-full min-h-screen transition-all duration-300 ease-in-out">
    <main class="flex-grow w-full max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 overflow-x-hidden">

    <?php if (Auth::check()): ?>
    <!-- Web Push Permission Prompt Banner -->
    <div id="pushPermissionBanner" class="hidden mb-6 bg-gradient-to-r from-indigo-900 via-indigo-800 to-indigo-900 text-white p-3.5 sm:p-4 rounded-2xl shadow-lg border border-indigo-700/60 relative z-20">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs sm:text-sm">
            <div class="flex items-center space-x-3">
                <span class="w-9 h-9 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center shrink-0 text-base shadow-inner">
                    <i class="fa-solid fa-bell"></i>
                </span>
                <div>
                    <strong class="text-white font-bold block sm:inline">📱 Mobil Çıkış Bildirimleri:</strong>
                    <span class="text-indigo-100"> Yarın çıkış yapacak misafirler için cep telefonunuza anlık bildirim almak ister misiniz?</span>
                </div>
            </div>
            <div class="flex items-center space-x-2 self-end sm:self-auto shrink-0">
                <button type="button" onclick="requestPushPermission()" class="bg-amber-400 hover:bg-amber-500 text-slate-950 font-bold px-4 py-2 rounded-xl transition active:scale-95 shadow-md text-xs cursor-pointer flex items-center space-x-1.5">
                    <i class="fa-solid fa-bell-ring"></i>
                    <span>Bildirimleri Aç</span>
                </button>
                <button type="button" onclick="dismissPushBanner()" class="text-indigo-200 hover:text-white px-2.5 py-2 rounded-xl text-xs transition cursor-pointer" title="Daha sonra">
                    Daha Sonra
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($flashError = Helpers::getFlash('error')): ?>
    <div class="mb-4 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg shadow-sm flex items-center justify-between text-rose-800">
        <div class="flex items-center space-x-3">
            <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
            <span class="font-medium text-sm"><?= Helpers::sanitize($flashError) ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if ($flashSuccess = Helpers::getFlash('success')): ?>
    <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm flex items-center justify-between text-emerald-800">
        <div class="flex items-center space-x-3">
            <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
            <span class="font-medium text-sm"><?= Helpers::sanitize($flashSuccess) ?></span>
        </div>
    </div>
<?php endif; ?>
