<?php
use App\Auth;
use App\Helpers;
$title = 'Dashboard - TakvimTakip';
require __DIR__ . '/../layouts/header.php';
?>

<!-- FullCalendar v6 -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/tr.global.min.js"></script>



<?php
$currentUser = Auth::user();
$licenseExpDate = null;
$remainingDays = null;
$userRole = $currentUser['role'] ?? 'owner';

if (!Auth::isAdmin() && $currentUser) {
    $db = \App\Database::getConnection();
    $stmt = $db->prepare("SELECT license_expires_at, role FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $currentUser['id']]);
    $uData = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($uData) {
        $licenseExpDate = $uData['license_expires_at'];
        $userRole = $uData['role'] ?? $userRole;
    }

    if ($licenseExpDate) {
        $today = date('Y-m-d');
        $remainingDays = (int)floor((strtotime($licenseExpDate) - strtotime($today)) / 86400);
    }
}

$isDemo = ($userRole === 'demo');
$is7DaysOrLess = ($remainingDays !== null && $remainingDays <= 7);

$showActionButton = (!Auth::isAdmin() && ($isDemo || $is7DaysOrLess));
$remDaysCount = max(0, (int)($remainingDays ?? 0));
$buttonLabel = $isDemo ? "Abonelik Satın Al" : "Aboneliği Uzat | Son {$remDaysCount} Gün";

$waText = $isDemo 
    ? "Merhaba, TakvimTakip hesabımdan Abonelik Satın Almak istiyorum."
    : "Merhaba, TakvimTakip lisans/aboneliğimi uzatmak istiyorum (Kalan Süre: {$remDaysCount} Gün).";
$waUrl = "https://wa.me/905437161546?text=" . urlencode($waText);
?>

<style>
@keyframes proShimmer {
    0% { transform: translateX(-100%) skewX(-12deg); }
    100% { transform: translateX(250%) skewX(-12deg); }
}
</style>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
    <div>
        <h1 class="text-lg sm:text-xl font-bold text-slate-800">Hoş Geldiniz, <?= Helpers::sanitize($user['full_name']) ?>&nbsp;👋</h1>
    </div>

    <?php if ($showActionButton): ?>
        <div class="w-full sm:w-auto shrink-0">
            <a href="<?= $waUrl ?>" target="_blank" rel="noopener noreferrer" class="relative overflow-hidden w-full sm:w-auto h-11 px-6 min-w-[160px] rounded-xl bg-[#f59e0b] hover:bg-[#d97706] text-white font-extrabold text-xs shadow-md shadow-amber-500/20 hover:shadow-lg transition-all flex items-center justify-center space-x-2.5 cursor-pointer active:scale-[0.99] border border-amber-400/40">
                <!-- Şık Kayan Işık Hüzmesi (Shimmer Sweep) -->
                <span class="absolute inset-0 w-1/2 h-full bg-gradient-to-r from-transparent via-white/35 to-transparent pointer-events-none" style="animation: proShimmer 2.5s infinite linear;"></span>
                
                <i class="fa-solid fa-crown text-amber-300 text-sm relative z-10 filter drop-shadow-xs"></i>
                <span class="tracking-wide whitespace-nowrap relative z-10"><?= $buttonLabel ?></span>
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Today's Moves Panel (Bugün Giriş & Çıkış Yapacak Misafirler) -->
<div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    <!-- Today's Check-ins (Bugün Giriş Yapacaklar) -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-emerald-100 flex flex-col justify-between space-y-4">
        <div>
            <div class="flex items-center justify-between border-b border-emerald-100 pb-3">
                <h2 class="text-sm sm:text-base font-bold text-slate-800 flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-right-to-bracket text-sm"></i>
                    </span>
                    <span>Bugün Giriş Yapacaklar</span>
                </h2>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-extrabold text-xs rounded-full border border-emerald-200/60">
                    <?= count($todayCheckins ?? []) ?> Misafir
                </span>
            </div>

            <div class="mt-4 space-y-3">
                <?php if (empty($todayCheckins)): ?>
                    <div class="text-center py-6 text-slate-400 text-xs">
                        <i class="fa-solid fa-circle-check text-2xl text-emerald-200 mb-1.5"></i>
                        <p class="font-medium text-slate-500">Bugün giriş yapacak misafir bulunmuyor.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($todayCheckins as $res): ?>
                        <?php
                        $isAgency = ($res['status'] === 'agency') 
                            || !empty($res['agency_name']) 
                            || in_array($res['source'] ?? '', ['acente', 'agency', 'airbnb', 'booking', 'external_ical']);
                        
                        $agencyName = !empty($res['agency_name']) 
                            ? $res['agency_name'] 
                            : ((($res['source'] ?? '') === 'acente' && !empty($res['customer_name'])) 
                                ? $res['customer_name'] 
                                : (!empty($res['source']) && $res['source'] !== 'manual' ? ucfirst($res['source']) : 'Acente'));

                        $guestName = $isAgency ? $agencyName : ($res['customer_name'] ?? 'Misafir');
                        $guestPhone = $res['customer_phone'] ?? '';
                        $cleanPhone = preg_replace('/[^0-9]/', '', $guestPhone);
                        if (strlen($cleanPhone) === 10) $cleanPhone = '90' . $cleanPhone;
                        
                        $remPay = (float)($res['remaining_payment'] ?? 0);
                        $checkinTime = !empty($res['checkin_time']) ? $res['checkin_time'] : '16:00';
                        $villaName = $res['villa_name'];

                        $waText = "Merhaba Sayın {$guestName},\n{$villaName} konutumuza bugün giriş gününüzdür. Giriş saatiniz: {$checkinTime}.";
                        if ($remPay > 0) {
                            $waText .= "\nKalan Ödeme Tutarınız: " . number_format($remPay, 0, ',', '.') . " ₺.";
                        }
                        $waText .= "\nKonum ve giriş detayları için bizimle iletişime geçebilirsiniz. İyi tatiller dileriz!";
                        $waLink = !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text=" . urlencode($waText) : "#";
                        ?>
                        <?php if ($isAgency): ?>
                            <div class="bg-emerald-50/40 border border-emerald-100 rounded-xl p-3.5 flex items-center justify-between gap-3 transition hover:bg-emerald-50/70">
                                <div class="flex items-center space-x-2 min-w-0">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-100/80 text-emerald-700 flex items-center justify-center shrink-0 text-xs">
                                        <i class="fa-solid fa-house"></i>
                                    </span>
                                    <span class="font-bold text-slate-800 text-xs sm:text-sm truncate">
                                        <?= Helpers::sanitize($villaName) ?>
                                    </span>
                                </div>
                                <div class="shrink-0 text-xs sm:text-sm font-bold text-slate-700">
                                    Acente Adı: <?= Helpers::sanitize($agencyName) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="bg-emerald-50/40 border border-emerald-100 rounded-xl p-3.5 flex items-center justify-between gap-3 transition hover:bg-emerald-50/70">
                                <div class="flex items-center space-x-2 min-w-0">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-100/80 text-emerald-700 flex items-center justify-center shrink-0 text-xs">
                                        <i class="fa-solid fa-house"></i>
                                    </span>
                                    <span class="font-bold text-slate-800 text-xs sm:text-sm truncate">
                                        <?= Helpers::sanitize($villaName) ?>
                                    </span>
                                </div>
                                <button type="button" onclick="openModalForDashboardReservation(<?= $res['id'] ?>, 'today')" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] rounded-lg shadow-sm transition active:scale-95 shrink-0" title="Rezervasyon Detayını Göster">
                                    <i class="fa-solid fa-circle-info text-xs"></i>
                                    <span>Detay</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Today's Check-outs (Bugün Çıkış Yapacaklar) -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-rose-100 flex flex-col justify-between space-y-4">
        <div>
            <div class="flex items-center justify-between border-b border-rose-100 pb-3">
                <h2 class="text-sm sm:text-base font-bold text-slate-800 flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i>
                    </span>
                    <span>Bugün Çıkış Yapacaklar</span>
                </h2>
                <span class="px-2.5 py-1 bg-rose-50 text-rose-700 font-extrabold text-xs rounded-full border border-rose-200/60">
                    <?= count($todayCheckouts ?? []) ?> Misafir
                </span>
            </div>

            <div class="mt-4 space-y-3">
                <?php if (empty($todayCheckouts)): ?>
                    <div class="text-center py-6 text-slate-400 text-xs">
                        <i class="fa-solid fa-circle-check text-2xl text-rose-200 mb-1.5"></i>
                        <p class="font-medium text-slate-500">Bugün çıkış yapacak misafir bulunmuyor.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($todayCheckouts as $res): ?>
                        <?php
                        $isAgency = ($res['status'] === 'agency') 
                            || !empty($res['agency_name']) 
                            || in_array($res['source'] ?? '', ['acente', 'agency', 'airbnb', 'booking', 'external_ical']);
                        
                        $agencyName = !empty($res['agency_name']) 
                            ? $res['agency_name'] 
                            : ((($res['source'] ?? '') === 'acente' && !empty($res['customer_name'])) 
                                ? $res['customer_name'] 
                                : (!empty($res['source']) && $res['source'] !== 'manual' ? ucfirst($res['source']) : 'Acente'));

                        $guestName = $isAgency ? $agencyName : ($res['customer_name'] ?? 'Misafir');
                        $guestPhone = $res['customer_phone'] ?? '';
                        $cleanPhone = preg_replace('/[^0-9]/', '', $guestPhone);
                        if (strlen($cleanPhone) === 10) $cleanPhone = '90' . $cleanPhone;
                        
                        $checkoutTime = !empty($res['checkout_time']) ? $res['checkout_time'] : '10:00';
                        $villaName = $res['villa_name'];
                        $villaDeposit = $res['villa_deposit'] ?? '';

                        $waText = "Merhaba Sayın {$guestName},\n{$villaName} konutumuzdaki konaklamanız bugün sona ermiştir. Çıkış saatiniz: {$checkoutTime}. Bizi tercih ettiğiniz için teşekkür ederiz, tekrar görüşmek dileğiyle!";
                        $waLink = !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text=" . urlencode($waText) : "#";
                        ?>
                        <?php if ($isAgency): ?>
                            <div class="bg-rose-50/40 border border-rose-100 rounded-xl p-3.5 flex items-center justify-between gap-3 transition hover:bg-rose-50/70">
                                <div class="flex items-center space-x-2 min-w-0">
                                    <span class="w-7 h-7 rounded-lg bg-rose-100/80 text-rose-700 flex items-center justify-center shrink-0 text-xs">
                                        <i class="fa-solid fa-house"></i>
                                    </span>
                                    <span class="font-bold text-slate-800 text-xs sm:text-sm truncate">
                                        <?= Helpers::sanitize($villaName) ?>
                                    </span>
                                </div>
                                <div class="shrink-0 text-xs sm:text-sm font-bold text-slate-700">
                                    Acente Adı: <?= Helpers::sanitize($agencyName) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="bg-rose-50/40 border border-rose-100 rounded-xl p-3.5 flex items-center justify-between gap-3 transition hover:bg-rose-50/70">
                                <div class="flex items-center space-x-2 min-w-0">
                                    <span class="w-7 h-7 rounded-lg bg-rose-100/80 text-rose-700 flex items-center justify-center shrink-0 text-xs">
                                        <i class="fa-solid fa-house"></i>
                                    </span>
                                    <span class="font-bold text-slate-800 text-xs sm:text-sm truncate">
                                        <?= Helpers::sanitize($villaName) ?>
                                    </span>
                                </div>
                                <button type="button" onclick="openModalForDashboardReservation(<?= $res['id'] ?>, 'today')" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] rounded-lg shadow-sm transition active:scale-95 shrink-0" title="Rezervasyon Detayını Göster">
                                    <i class="fa-solid fa-circle-info text-xs"></i>
                                    <span>Detay</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Master Closed & Booked Dates Panel by Villa -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 space-y-5">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800 flex items-center space-x-2">
                <i class="fa-solid fa-lock text-slate-700"></i>
                <span>Kapatılan & Rezerve Tarihler</span>
            </h2>
        </div>

        <?php if (!empty($villas)): ?>
            <!-- Villa Select Dropdown & Icon-Only Calendar Button -->
            <div class="flex items-center space-x-2 shrink-0">
                <label for="dashboardVillaSelect" class="text-xs font-bold text-slate-700 whitespace-nowrap">Konut Seçin:</label>
                <select id="dashboardVillaSelect" onchange="loadDashboardVillaReservations(this.value)" class="h-9 min-w-[140px] max-w-[200px] sm:max-w-[260px] px-2.5 rounded-xl border border-indigo-200 text-xs font-bold text-indigo-900 bg-indigo-50/60 outline-none focus:ring-2 focus:ring-indigo-500 truncate cursor-pointer">
                    <option value="all" selected>Tüm Konutlar</option>
                    <?php foreach ($villas as $villa): ?>
                        <option value="<?= $villa['id'] ?>">
                            <?= Helpers::sanitize($villa['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="btnDashboardCalendarModal" onclick="openCalendarModalPopup()" disabled class="h-9 px-3.5 bg-slate-100 border border-slate-200 text-slate-400 font-bold text-xs rounded-xl transition inline-flex items-center justify-center space-x-1.5 whitespace-nowrap shrink-0 opacity-50 cursor-not-allowed pointer-events-none" title="Takvim butonunu kullanmak için lütfen bir konut seçin">
                    <i class="fa-solid fa-calendar-days text-xs"></i>
                    <span class="sm:hidden">Takvim</span>
                    <span class="hidden sm:inline">Müsaitlik Takvimi</span>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($villas)): ?>
        <div class="text-center py-12 bg-slate-50 rounded-xl border border-dashed border-slate-200">
            <i class="fa-solid fa-house-circle-exclamation text-3xl text-slate-300 mb-2"></i>
            <p class="text-slate-600 text-sm font-medium">Henüz hiçbir konut eklemediniz.</p>
            <a href="<?= APP_URL ?>/villas/new" class="inline-block mt-3 px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold">İlk Konutunuzu Ekleyin</a>
        </div>
    <?php else: ?>
        <!-- Search Bar Row -->
        <div class="w-full relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <input type="text" id="dashboardSearchInput" oninput="filterDashboardReservations()" placeholder="Giriş tarihi ara (Örn: 27.08, 02.09, Eylül...)" class="w-full pl-8 pr-8 py-2.5 rounded-xl border border-slate-200 text-xs font-medium outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/60 placeholder-slate-400">
            <button id="dashboardClearSearchBtn" onclick="clearDashboardSearch()" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs font-bold">&times;</button>
        </div>

        <!-- Reservations List Grid / Cards (Max 3 rows on desktop with scrollbar) -->
        <div id="dashboardReservationsList" class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[220px] overflow-y-auto pr-1">
            <div class="col-span-full text-center py-8 text-slate-400 text-xs">
                <i class="fa-solid fa-spinner animate-spin text-xl text-indigo-500 mb-2"></i>
                <p>Kayıtlar yükleniyor...</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Upcoming Reservations Panel by Villa (Next 7 Days / Son 1 Hafta İçi Girişler) -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 space-y-5 mt-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800 flex items-center space-x-2">
                <i class="fa-solid fa-clock text-amber-500"></i>
                <span>Yaklaşan Rezervasyonlar</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Seçtiğiniz konuta ait önümüzdeki 7 gün içinde gerçekleşecek rezervasyonlar listelenmektedir.</p>
        </div>

        <?php if (!empty($villas)): ?>
            <!-- Villa Select Dropdown for Upcoming Reservations (Aligned Right on Desktop) -->
            <div class="flex items-center space-x-2 shrink-0">
                <label for="upcomingVillaSelect" class="text-xs font-bold text-slate-700 whitespace-nowrap">Konut Seçin:</label>
                <select id="upcomingVillaSelect" onchange="loadUpcomingVillaReservations(this.value)" class="h-9 px-3.5 rounded-xl border border-indigo-200 text-xs font-bold text-indigo-900 bg-indigo-50/60 outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="all" selected>Tüm Konutlarım</option>
                    <?php foreach ($villas as $idx => $villa): ?>
                        <option value="<?= $villa['id'] ?>">
                            <?= Helpers::sanitize($villa['name']) ?> (<?= Helpers::sanitize($villa['city']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming Reservations Grid (Max 3 rows on desktop with scrollbar) -->
    <div id="upcomingReservationsList" class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[220px] overflow-y-auto pr-1">
        <div class="col-span-full text-center py-8 text-slate-400 text-xs">
            <i class="fa-solid fa-spinner animate-spin text-xl text-indigo-500 mb-2"></i>
            <p>Yaklaşan rezervasyonlar yükleniyor...</p>
        </div>
    </div>
</div>

<!-- FullCalendar Müsaitlik Takvimi Pop-Up Modal (Scrollsuz & Sadece Mevcut Ay Tarihleri) -->
<div id="calendarModalPopup" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl p-4 sm:p-5 border border-slate-100 overflow-hidden transform transition-all space-y-2.5">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 inline-block"></span>
                <div>
                    <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wide" id="popupCalendarVillaName">--</div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-none mt-0.5">Müsaitlik Takvimi</h3>
                </div>
            </div>
            <button onclick="closeCalendarModalPopup()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition flex items-center justify-center text-base font-bold">&times;</button>
        </div>

        <!-- Legend -->
        <div class="bg-slate-50 px-3 py-2 rounded-xl flex items-center justify-between text-xs w-full">
            <div class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-white border border-slate-400 inline-block"></span><span class="font-medium text-slate-700">Müsait</span></div>
            <div class="flex items-center space-x-1.5"><span class="w-3.5 h-3.5 rounded-full border border-slate-300 inline-block shrink-0" style="background: linear-gradient(135deg, #fd5a5f 50%, #ffffff 50%);"></span><span class="font-medium text-slate-700">Giriş / Çıkış</span></div>
            <div class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-[#fd5a5f] inline-block"></span><span class="font-medium text-slate-700">Rezerve</span></div>
        </div>

        <!-- Compact Calendar Container -->
        <div class="bg-white rounded-xl">
            <div id="popupCalendar"></div>
        </div>
    </div>
</div>

<!-- Action Modal / Edit Popup for Dashboard -->
<div id="dashboardModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 hidden overflow-y-auto p-3 sm:p-6">
    <div class="min-h-full flex items-center justify-center">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-100 relative my-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 bg-indigo-50 text-indigo-700 rounded-md text-[11px] font-bold mb-1">
                        <i class="fa-solid fa-house text-[10px]"></i>
                        <span id="dashModalVillaName">--</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800" id="dashModalTitle">Rezervasyon Detayı / Düzenle</h3>
                    <div class="text-xs font-semibold text-indigo-600 mt-0.5" id="dashSelectedDateText">-- / --</div>
                </div>
                <button type="button" onclick="closeDashboardModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center text-sm font-bold shrink-0">&times;</button>
            </div>

            <!-- Dynamic Error Banner in Modal -->
            <div id="dashModalError" class="hidden mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl"></div>

            <form id="dashReservationForm" onsubmit="handleDashboardReservationSubmit(event)" class="space-y-4">
                <input type="hidden" name="villa_id" id="dashInputVillaId">
                <input type="hidden" name="start_date" id="dashInputStartDate">
                <input type="hidden" name="end_date" id="dashInputEndDate">
                <input type="hidden" name="reservation_id" id="dashInputReservationId">

                <!-- Action Selector Buttons -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">İşlem Türü</label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <!-- Button 1: Acente Rezervasyonu -->
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="agency" onclick="toggleDashFields('agency')" class="peer sr-only">
                            <div class="py-2.5 px-1.5 text-center font-bold text-[11px] sm:text-xs rounded-xl border-2 border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 transition whitespace-nowrap flex items-center justify-center space-x-1">
                                <i class="fa-solid fa-briefcase text-indigo-600 text-[11px] sm:text-xs shrink-0"></i>
                                <span>Acente Rezervasyonu</span>
                            </div>
                        </label>
                        <!-- Button 2: Ev Sahibi Rezervasyonu -->
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="booked" onclick="toggleDashFields('booked')" class="peer sr-only">
                            <div class="py-2.5 px-1.5 text-center font-bold text-[11px] sm:text-xs rounded-xl border-2 border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 transition whitespace-nowrap flex items-center justify-center space-x-1">
                                <i class="fa-solid fa-user-shield text-rose-500 text-[11px] sm:text-xs shrink-0"></i>
                                <span>Ev Sahibi Rezervasyonu</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Booking Specific Fields -->
                <div id="dashBookingFields" class="space-y-3 pt-2">
                    <!-- Main hidden booking_date -->
                    <input type="hidden" name="booking_date" id="dashInputBookingDate">

                    <!-- Acente Adı Kutusu -->
                    <div id="dashAgencyNameBox" class="hidden">
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div class="min-w-0">
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate" title="Acente Adı (İsteğe Bağlı)">Acente Adı</label>
                                <input type="text" name="agency_name" id="dashInputAgencyName" placeholder="Örn: TUI, Mavitur" class="w-full min-w-0 max-w-full px-2.5 sm:px-3 py-2 rounded-xl border border-indigo-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500 bg-indigo-50/40 font-medium">
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate" title="Rezervasyon Alınma Tarihi">Rez. Alınma Tarihi</label>
                                <input type="date" id="dashInputBookingDateAgency" disabled onchange="syncDashBookingDate('agency')" class="w-full min-w-0 max-w-full px-2 sm:px-3 py-2 rounded-xl border border-indigo-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-indigo-50/40">
                            </div>
                        </div>
                    </div>

                    <!-- Müşteri & Ödeme Detay Kutuları -->
                    <div id="dashCustomerAndPriceFields" class="space-y-3">
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div class="min-w-0">
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Müşteri Ad Soyad</label>
                                <input type="text" name="customer_name" id="dashInputCustomerName" placeholder="Ahmet Bey" class="w-full min-w-0 max-w-full px-2.5 sm:px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Telefon Numarası</label>
                                <input type="tel" name="customer_phone" id="dashInputCustomerPhone" placeholder="0532 000 00 00" class="w-full min-w-0 max-w-full px-2.5 sm:px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div class="min-w-0">
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate" title="Rezervasyon Alınma Tarihi">Rez. Alınma Tarihi</label>
                                <input type="date" id="dashInputBookingDateOwner" disabled onchange="syncDashBookingDate('owner')" class="w-full min-w-0 max-w-full px-2 sm:px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50">
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Rezervasyon Kaynağı</label>
                                <select name="source" id="dashInputSource" class="w-full min-w-0 max-w-full px-2 sm:px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-1.5 sm:gap-3">
                            <div class="min-w-0">
                                <label class="block text-[10px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Toplam Fiyat</label>
                                <div class="relative flex items-center">
                                    <input type="text" name="price" id="dashInputPrice" oninput="handleMoneyInput(this)" placeholder="0" class="w-full min-w-0 max-w-full pl-2 sm:pl-3 pr-6 py-2 rounded-xl border border-slate-300 text-[11px] sm:text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                                    <span class="absolute right-2.5 text-slate-400 font-bold text-xs pointer-events-none">₺</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[10px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Ön Ödeme</label>
                                <div class="relative flex items-center">
                                    <input type="text" name="down_payment" id="dashInputDownPayment" oninput="handleMoneyInput(this)" placeholder="0" class="w-full min-w-0 max-w-full pl-2 sm:pl-3 pr-6 py-2 rounded-xl border border-slate-300 text-[11px] sm:text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                                    <span class="absolute right-2.5 text-slate-400 font-bold text-xs pointer-events-none">₺</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[10px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Kalan Ödeme</label>
                                <div class="relative flex items-center">
                                    <input type="text" name="remaining_payment" id="dashInputRemainingPayment" oninput="handleMoneyInput(this)" placeholder="0" class="w-full min-w-0 max-w-full pl-2 sm:pl-3 pr-6 py-2 rounded-xl border border-slate-300 text-[11px] sm:text-xs font-bold text-emerald-700 bg-emerald-50/50 outline-none focus:ring-2 focus:ring-indigo-500">
                                    <span class="absolute right-2.5 text-emerald-600 font-bold text-xs pointer-events-none">₺</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Not / Özel İstekler</label>
                            <textarea name="note" id="dashInputNote" rows="2" placeholder="Örn: 2 çocuklu aile, bebek yatağı eklenecek." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit & Action buttons -->
                <div id="dashModalButtons" class="pt-3 flex items-center space-x-2">
                    <button type="submit" id="dashBtnSubmitForm" class="flex-grow py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-lg transition active:scale-[0.99] flex items-center justify-center space-x-1.5">
                        <span id="dashBtnSubmitText">Güncelle</span>
                    </button>

                    <a id="dashCallModalBtn" href="#" class="hidden flex-grow py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-lg transition active:scale-[0.99] flex items-center justify-center space-x-1.5 shrink-0">
                        <i class="fa-solid fa-phone text-sm"></i>
                        <span>Misafiri Ara</span>
                    </a>

                    <a id="dashWhatsappBtn" href="#" target="_blank" class="hidden py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5 active:scale-[0.99] shrink-0">
                        <i class="fa-brands fa-whatsapp text-sm sm:text-base"></i>
                        <span>WhatsApp'tan Yaz</span>
                    </a>

                    <button type="button" id="dashBtnCancelModal" onclick="closeDashboardModal()" class="hidden py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition shrink-0">
                        Kapat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const appUrl = '<?= APP_URL ?>';
let currentDashboardVillaId = null;
let masterDashboardReservations = <?= json_encode($allReservations ?? []) ?>;
let popupCalendarInstance = null;
const masterVillasList = <?= json_encode($villas ?? []) ?>;

function getVillaObj(villaId) {
    const v = masterVillasList.find(item => item.id == villaId);
    if (v) return v;
    return {
        name: document.getElementById('dashModalVillaName')?.innerText || '',
        checkin_time: '',
        checkout_time: '',
        deposit_amount: '',
        pool_heating_fee: ''
    };
}

const sourceLabels = {
    'eski_misafir': 'Eski Misafir',
    'instagram': 'İnstagram',
    'google': 'Google',
    'airbnb': 'Airbnb',
    'vrbo': 'Vrbo',
    'tavsiye': 'Tavsiye',
    'sosyal_medya': 'Sosyal Medya',
    'web_sitem': 'Web Sitem',
    'external_ical': 'Dış Acente',
    'booking': 'Booking.com',
    'acente': 'Acente'
};

function getSourceLabel(code) {
    if (!code) return '';
    return sourceLabels[code] || code.toUpperCase();
}

function formatDateTR(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length === 3) {
        return `${parts[2]}.${parts[1]}.${parts[0]}`;
    }
    return dateStr;
}

function getTodayString() {
    const today = new Date();
    const y = today.getFullYear();
    const m = String(today.getMonth() + 1).padStart(2, '0');
    const d = String(today.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function sortReservationsByNearest(list) {
    const todayStr = getTodayString();
    return (list || []).sort((a, b) => {
        const aStart = a.start_date || '';
        const aEnd = a.end_date || aStart;
        const bStart = b.start_date || '';
        const bEnd = b.end_date || bStart;

        const aIsUpcoming = (aEnd >= todayStr);
        const bIsUpcoming = (bEnd >= todayStr);

        if (aIsUpcoming && !bIsUpcoming) return -1;
        if (!aIsUpcoming && bIsUpcoming) return 1;

        if (aIsUpcoming && bIsUpcoming) {
            if (aStart !== bStart) return aStart.localeCompare(bStart);
            return (a.id || 0) - (b.id || 0);
        } else {
            if (aStart !== bStart) return bStart.localeCompare(aStart);
            return (b.id || 0) - (a.id || 0);
        }
    });
}

function setDashBookingDateValue(val) {
    const today = val || getTodayString();
    document.getElementById('dashInputBookingDate').value = today;
    const agencyInput = document.getElementById('dashInputBookingDateAgency');
    const ownerInput = document.getElementById('dashInputBookingDateOwner');
    if (agencyInput) agencyInput.value = today;
    if (ownerInput) ownerInput.value = today;
}

function syncDashBookingDate(type) {
    if (type === 'agency') {
        const val = document.getElementById('dashInputBookingDateAgency').value;
        setDashBookingDateValue(val);
    } else {
        const val = document.getElementById('dashInputBookingDateOwner').value;
        setDashBookingDateValue(val);
    }
}

function parseMoneyTR(val) {
    if (!val) return 0;
    let clean = String(val).replace(/[^0-9]/g, '');
    return parseFloat(clean) || 0;
}

function formatMoneyTR(val) {
    if (val === null || val === undefined || val === '') return '';
    let num = typeof val === 'number' ? val : parseMoneyTR(val);
    if (isNaN(num) || num === 0) return '';
    return new Intl.NumberFormat('tr-TR').format(num);
}

function handleMoneyInput(inputEl) {
    let oldVal = inputEl.value;
    let oldPos = inputEl.selectionStart || 0;

    let raw = parseMoneyTR(oldVal);
    if (raw <= 0) {
        inputEl.value = '';
    } else {
        inputEl.value = new Intl.NumberFormat('tr-TR').format(raw);
    }

    let newVal = inputEl.value;
    let addedChars = newVal.length - oldVal.length;
    let newPos = Math.max(0, oldPos + addedChars);
    if (inputEl.setSelectionRange && document.activeElement === inputEl) {
        try {
            inputEl.setSelectionRange(newPos, newPos);
        } catch (e) {}
    }

    calcDashRemainingPayment();
}

function calcDashRemainingPayment() {
    const price = parseMoneyTR(document.getElementById('dashInputPrice').value);
    const down = parseMoneyTR(document.getElementById('dashInputDownPayment').value);
    if (price > 0 || down > 0) {
        const remaining = Math.max(0, price - down);
        document.getElementById('dashInputRemainingPayment').value = remaining > 0 ? new Intl.NumberFormat('tr-TR').format(remaining) : '';
    } else {
        document.getElementById('dashInputRemainingPayment').value = '';
    }
}

function updateCalendarButtonState(villaId) {
    const btn = document.getElementById('btnDashboardCalendarModal');
    if (!btn) return;

    if (!villaId || villaId === 'all') {
        btn.disabled = true;
        btn.title = "Takvim butonunu kullanmak için lütfen bir konut seçin";
        btn.className = "h-9 px-3.5 bg-slate-100 border border-slate-200 text-slate-400 font-bold text-xs rounded-xl transition inline-flex items-center justify-center space-x-1.5 whitespace-nowrap shrink-0 opacity-50 cursor-not-allowed pointer-events-none";
    } else {
        btn.disabled = false;
        btn.title = "Müsaitlik Takvimi";
        btn.className = "h-9 px-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition inline-flex items-center justify-center space-x-1.5 whitespace-nowrap shrink-0 cursor-pointer";
    }
}

function loadDashboardVillaReservations(villaId) {
    currentDashboardVillaId = villaId;
    updateCalendarButtonState(villaId);
    filterDashboardReservations();
}

function filterDashboardReservations() {
    const input = document.getElementById('dashboardSearchInput');
    const clearBtn = document.getElementById('dashboardClearSearchBtn');
    const query = input ? input.value.trim().toLowerCase() : '';

    if (clearBtn) {
        if (query) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    let listToFilter = masterDashboardReservations || [];
    if (currentDashboardVillaId && currentDashboardVillaId !== 'all') {
        listToFilter = listToFilter.filter(r => r.villa_id == currentDashboardVillaId);
    }

    listToFilter = sortReservationsByNearest(listToFilter);

    if (!query) {
        renderDashboardReservations(listToFilter, false);
        return;
    }

    const monthsTR = ['', 'ocak', 'şubat', 'mart', 'nisan', 'mayıs', 'haziran', 'temmuz', 'ağustos', 'eylül', 'ekim', 'kasım', 'aralık'];

    const filtered = listToFilter.filter(item => {
        const startTr = formatDateTR(item.start_date).toLowerCase();
        const startDateIso = item.start_date ? item.start_date.toLowerCase() : '';
        const endTr = formatDateTR(item.end_date).toLowerCase();

        let labelText = 'Ev Sahibi';
        if (item.status === 'blocked') labelText = 'Kapalı / Bloke';
        else if (item.status === 'optional') labelText = 'Opsiyonlu';
        else if (item.agency_name || ['agency', 'acente', 'airbnb', 'booking', 'external_ical'].includes(item.source)) {
            const agencyTitle = item.agency_name || (item.customer_name && item.source === 'acente' ? item.customer_name : '');
            labelText = agencyTitle ? `Acente: ${agencyTitle}` : 'Acente';
        }
        labelText = labelText.toLowerCase();

        const startMonthIndex = item.start_date ? parseInt(item.start_date.split('-')[1], 10) : 0;
        const monthName = monthsTR[startMonthIndex] || '';

        return startTr.includes(query) || 
               startDateIso.includes(query) || 
               endTr.includes(query) || 
               labelText.includes(query) || 
               monthName.includes(query) ||
               (item.customer_name && item.customer_name.toLowerCase().includes(query)) ||
               (item.agency_name && item.agency_name.toLowerCase().includes(query)) ||
               (item.villa_name && item.villa_name.toLowerCase().includes(query));
    });

    renderDashboardReservations(filtered, true);
}

function clearDashboardSearch() {
    const input = document.getElementById('dashboardSearchInput');
    if (input) {
        input.value = '';
        filterDashboardReservations();
    }
}

function renderDashboardReservations(events, isFiltered = false) {
    const listContainer = document.getElementById('dashboardReservationsList');
    const badge = document.getElementById('dashboardClosedBadge');

    const displayEvents = isFiltered ? events : (events || []).slice(0, 10);

    if (badge) {
        if (isFiltered) {
            badge.innerText = `${events.length} / ${masterDashboardReservations.length} kayıt`;
        } else {
            if (masterDashboardReservations.length > 10) {
                badge.innerText = `Son 10 / ${masterDashboardReservations.length} kayıt`;
            } else {
                badge.innerText = `${masterDashboardReservations.length} kayıt`;
            }
        }
    }

    if (!displayEvents || displayEvents.length === 0) {
        listContainer.innerHTML = `
            <div class="col-span-2 text-center py-10 text-slate-400 text-xs">
                <i class="fa-regular fa-calendar-xmark text-3xl text-slate-300 mb-2"></i>
                <p class="font-medium">${isFiltered ? 'Arama kriterinize uygun kapalı tarih bulunamadı.' : 'Seçilen seçenekte henüz kayıtlı kapalı veya rezerve tarih bulunmuyor.'}</p>
            </div>
        `;
        return;
    }

    let html = '';
    displayEvents.forEach(item => {
        let badgeClass = 'bg-slate-50 text-slate-800 border-slate-200';
        let icon = 'fa-lock text-slate-600';

        if (item.status === 'booked') {
            badgeClass = 'bg-rose-50 text-rose-800 border-rose-200';
            icon = 'fa-circle-check text-rose-500';
        } else if (item.status === 'optional') {
            badgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
            icon = 'fa-clock text-amber-500';
        }

        const startTr = formatDateTR(item.start_date);
        const endTr = formatDateTR(item.end_date);
        
        let labelText = 'Ev Sahibi';
        if (item.status === 'blocked') {
            labelText = 'Kapalı / Bloke';
        } else if (item.status === 'optional') {
            labelText = 'Opsiyonlu';
        } else if (item.agency_name || ['agency', 'acente', 'airbnb', 'booking', 'external_ical'].includes(item.source)) {
            const agencyTitle = item.agency_name || (item.customer_name && item.source === 'acente' ? item.customer_name : '');
            labelText = agencyTitle ? `Acente: ${agencyTitle}` : 'Acente';
        } else {
            labelText = 'Ev Sahibi';
        }

        const villaLabel = item.villa_name && currentDashboardVillaId === 'all' ? `<span class="text-indigo-700 font-bold ml-1">(${item.villa_name})</span>` : '';

        html += `
            <div class="p-3.5 rounded-xl border ${badgeClass} text-xs flex items-center justify-between gap-3 transition hover:shadow-sm">
                <div class="min-w-0 flex-1">
                    <div class="font-bold flex items-center space-x-1.5 truncate text-slate-900">
                        <i class="fa-solid ${icon} text-[11px] flex-shrink-0"></i>
                        <span class="truncate">${startTr} ➔ ${endTr}</span>
                    </div>
                    <div class="text-[11px] font-semibold mt-0.5 text-slate-600 truncate">${labelText} ${villaLabel}</div>
                </div>
                <div class="flex items-center space-x-1 flex-shrink-0">
                    <button onclick="openModalForDashboardReservation(${item.id})" class="px-2.5 py-1 bg-white hover:bg-indigo-600 hover:text-white text-indigo-700 font-bold rounded-lg border border-slate-200 text-[11px] shadow-sm transition" title="Detay / Düzenle">
                        Detay
                    </button>
                    <button onclick="quickDeleteDashboardReservation(${item.id})" class="px-2 py-1 bg-white hover:bg-rose-600 hover:text-white text-rose-700 font-bold rounded-lg border border-slate-200 text-[11px] shadow-sm transition" title="Tarihi Aç / Sil">
                        Aç / Sil
                    </button>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
}

const dashboardReservationsCache = {};

function openCalendarModalPopup() {
    const selectEl = document.getElementById('dashboardVillaSelect');
    if (!selectEl || !selectEl.value || selectEl.value === 'all') return;

    const villaId = selectEl.value;
    currentDashboardVillaId = villaId;
    const villaName = selectEl.options[selectEl.selectedIndex] ? selectEl.options[selectEl.selectedIndex].text : '';

    document.getElementById('popupCalendarVillaName').innerText = villaName;
    document.getElementById('calendarModalPopup').classList.remove('hidden');

    requestAnimationFrame(() => {
        if (!popupCalendarInstance) {
            const calendarEl = document.getElementById('popupCalendar');
            popupCalendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'tr',
                selectable: true,
                selectMirror: true,
                fixedWeekCount: false,
                showNonCurrentDates: false,
                stickyHeaderDates: false,
                height: 'auto',
                aspectRatio: 1.0,
                expandRows: false,
                headerToolbar: {
                    left: 'prev',
                    center: 'title',
                    right: 'today next'
                },
                buttonText: {
                    today: 'Bugün',
                    month: 'Ay',
                    list: 'Liste'
                },
                datesSet: function(info) {
                    const cached = dashboardReservationsCache[currentDashboardVillaId];
                    if (cached && cached.length > 0) {
                        requestAnimationFrame(() => {
                            applyPopupCalendarCellStyles(cached);
                        });
                    }
                },
                events: function(info, successCallback, failureCallback) {
                    const cached = dashboardReservationsCache[currentDashboardVillaId];
                    if (cached && cached.length > 0) {
                        const formattedEvents = cached.map(item => ({
                            id: item.id,
                            start: item.start_date,
                            end: item.end_date,
                            display: 'background',
                            extendedProps: item
                        }));
                        successCallback(formattedEvents);
                        requestAnimationFrame(() => {
                            applyPopupCalendarCellStyles(cached);
                        });
                    }

                    fetch(`${appUrl}/api/villas/${currentDashboardVillaId}/all-reservations`)
                        .then(res => res.json())
                        .then(data => {
                            dashboardReservationsCache[currentDashboardVillaId] = Array.isArray(data) ? data : [];
                            const updated = dashboardReservationsCache[currentDashboardVillaId];
                            requestAnimationFrame(() => {
                                applyPopupCalendarCellStyles(updated);
                            });

                            if (!cached || cached.length === 0) {
                                const formattedEvents = updated.map(item => ({
                                    id: item.id,
                                    start: item.start_date,
                                    end: item.end_date,
                                    display: 'background',
                                    extendedProps: item
                                }));
                                successCallback(formattedEvents);
                            }
                        })
                        .catch(err => {
                            if (!cached || cached.length === 0) {
                                failureCallback(err);
                            }
                        });
                },
                dateClick: function(info) {
                    const todayStr = getTodayString();
                    if (info.dateStr < todayStr) {
                        alert('Geçmiş tarihlere yeni rezervasyon veya kapatma işlemi yapılamaz.');
                        return;
                    }
                    closeCalendarModalPopup();
                    openDashboardModalForNew(info.dateStr, info.dateStr);
                },
                eventClick: function(info) {
                    closeCalendarModalPopup();
                    const props = info.event.extendedProps;
                    openModalForDashboardReservation(props.id);
                }
            });
            popupCalendarInstance.render();
        } else {
            popupCalendarInstance.today();
            popupCalendarInstance.refetchEvents();
            popupCalendarInstance.updateSize();
        }
    });
}

function applyPopupCalendarCellStyles(reservations) {
    document.querySelectorAll('#popupCalendar .fc-daygrid-day').forEach(el => {
        el.classList.remove('fc-has-reservation');
        el.style.background = 'transparent';
        const frame = el.querySelector('.fc-daygrid-day-frame') || el;
        const numEl = el.querySelector('.fc-daygrid-day-number');

        frame.style.removeProperty('background');
        frame.style.removeProperty('border-color');
        frame.style.removeProperty('box-shadow');

        if (numEl) {
            numEl.style.removeProperty('color');
            numEl.style.removeProperty('text-shadow');
            numEl.style.removeProperty('font-weight');
        }

        if (el.classList.contains('fc-day-other')) {
            frame.style.setProperty('background', 'transparent', 'important');
            frame.style.setProperty('border', 'none', 'important');
            frame.style.setProperty('border-color', 'transparent', 'important');
            frame.style.setProperty('opacity', '0', 'important');
            frame.style.setProperty('visibility', 'hidden', 'important');
            frame.style.setProperty('pointer-events', 'none', 'important');
        } else {
            frame.style.setProperty('background', '#ffffff', 'important');
            frame.style.removeProperty('opacity');
            frame.style.removeProperty('pointer-events');
        }
    });

    const dayMap = {};

    reservations.forEach(item => {
        let color = '#fd5a5f';
        if (item.status === 'blocked') color = '#fd5a5f';
        else if (item.status === 'optional') color = '#fef3c7';

        const start = item.start_date;
        const end = item.end_date;
        if (!start || !end) return;

        if (!dayMap[start]) dayMap[start] = {};
        dayMap[start].inColor = color;

        if (!dayMap[end]) dayMap[end] = {};
        dayMap[end].outColor = color;

        let curr = new Date(start + 'T00:00:00');
        curr.setDate(curr.getDate() + 1);
        const endDateObj = new Date(end + 'T00:00:00');

        while (curr < endDateObj) {
            const y = curr.getFullYear();
            const m = String(curr.getMonth() + 1).padStart(2, '0');
            const d = String(curr.getDate()).padStart(2, '0');
            const dStr = `${y}-${m}-${d}`;

            if (!dayMap[dStr]) dayMap[dStr] = {};
            dayMap[dStr].isFull = true;
            dayMap[dStr].fullColor = color;

            curr.setDate(curr.getDate() + 1);
        }
    });

    Object.keys(dayMap).forEach(dateStr => {
        const cell = document.querySelector(`#popupCalendar .fc-daygrid-day[data-date="${dateStr}"]`);
        if (!cell || cell.classList.contains('fc-day-other')) return;

        const frame = cell.querySelector('.fc-daygrid-day-frame') || cell;
        const info = dayMap[dateStr];
        const numEl = cell.querySelector('.fc-daygrid-day-number');

        if (info.outColor && info.inColor) {
            cell.classList.add('fc-has-reservation');
            frame.style.setProperty('background', `linear-gradient(135deg, ${info.outColor} calc(50% - 3px), #ffffff calc(50% - 3px), #ffffff calc(50% + 3px), ${info.inColor} calc(50% + 3px))`, 'important');
            if (numEl) {
                numEl.style.setProperty('color', '#ffffff', 'important');
                numEl.style.setProperty('text-shadow', '0 1px 2px rgba(0, 0, 0, 0.5)', 'important');
                numEl.style.setProperty('font-weight', '800', 'important');
            }
        } else if (info.isFull) {
            cell.classList.add('fc-has-reservation');
            frame.style.setProperty('background', info.fullColor, 'important');
            if (numEl) {
                numEl.style.setProperty('color', '#ffffff', 'important');
                numEl.style.setProperty('text-shadow', '0 1px 2px rgba(0, 0, 0, 0.5)', 'important');
                numEl.style.setProperty('font-weight', '800', 'important');
            }
        } else if (info.outColor) {
            cell.classList.remove('fc-has-reservation');
            frame.style.setProperty('background', `linear-gradient(135deg, ${info.outColor} 49%, #ffffff 51%)`, 'important');
            if (numEl) {
                numEl.style.setProperty('color', '#1e293b', 'important');
                numEl.style.removeProperty('text-shadow');
                numEl.style.setProperty('font-weight', '700', 'important');
            }
        } else if (info.inColor) {
            cell.classList.remove('fc-has-reservation');
            frame.style.setProperty('background', `linear-gradient(135deg, #ffffff 49%, ${info.inColor} 51%)`, 'important');
            if (numEl) {
                numEl.style.setProperty('color', '#1e293b', 'important');
                numEl.style.removeProperty('text-shadow');
                numEl.style.setProperty('font-weight', '700', 'important');
            }
        }
    });
}

function closeCalendarModalPopup() {
    document.getElementById('calendarModalPopup').classList.add('hidden');
}

function openModalForDashboardReservation(id, mode = 'edit') {
    let item = masterDashboardReservations.find(r => r.id == id);
    if (!item) {
        item = masterUpcomingReservations.find(r => r.id == id);
    }
    if (!item) return;

    const dashModalButtons = document.getElementById('dashModalButtons');
    const submitBtn = document.getElementById('dashBtnSubmitForm');
    const dashBtnSubmitText = document.getElementById('dashBtnSubmitText');
    const callBtn = document.getElementById('dashCallModalBtn');
    const cancelBtn = document.getElementById('dashBtnCancelModal');
    const waBtn = document.getElementById('dashWhatsappBtn');

    if (dashModalButtons) dashModalButtons.classList.remove('hidden');
    if (cancelBtn) cancelBtn.classList.add('hidden');

    if (mode === 'today') {
        if (submitBtn) submitBtn.classList.add('hidden');
        document.getElementById('dashModalTitle').innerText = 'Rezervasyon Detayı';
    } else {
        if (submitBtn) submitBtn.classList.remove('hidden');
        if (dashBtnSubmitText) dashBtnSubmitText.innerText = 'Güncelle';
        document.getElementById('dashModalTitle').innerText = 'Rezervasyon Detayı / Düzenle';
    }

    const itemVillaId = item.villa_id || (currentDashboardVillaId !== 'all' ? currentDashboardVillaId : '');

    const selectEl = document.getElementById('dashboardVillaSelect');
    let selectedVillaName = item.villa_name;
    if (!selectedVillaName && selectEl && selectEl.options[selectEl.selectedIndex]) {
        selectedVillaName = selectEl.options[selectEl.selectedIndex].text;
    }
    document.getElementById('dashModalVillaName').innerText = selectedVillaName || '--';

    document.getElementById('dashInputVillaId').value = itemVillaId;
    document.getElementById('dashInputStartDate').value = item.start_date;
    document.getElementById('dashInputEndDate').value = item.end_date;
    document.getElementById('dashInputReservationId').value = item.id;
    document.getElementById('dashSelectedDateText').innerText = `${formatDateTR(item.start_date)}  ➔  ${formatDateTR(item.end_date)}`;
    document.getElementById('dashModalError').classList.add('hidden');

    document.getElementById('dashInputCustomerName').value = item.customer_name || '';
    document.getElementById('dashInputCustomerPhone').value = item.customer_phone || '';
    document.getElementById('dashInputAgencyName').value = item.agency_name || '';
    
    const bookingDateVal = item.booking_date || (item.created_at ? item.created_at.substring(0, 10) : getTodayString());
    setDashBookingDateValue(bookingDateVal);

    document.getElementById('dashInputPrice').value = formatMoneyTR(item.price);
    document.getElementById('dashInputDownPayment').value = formatMoneyTR(item.down_payment);
    document.getElementById('dashInputRemainingPayment').value = formatMoneyTR(item.remaining_payment);
    document.getElementById('dashInputNote').value = item.note || '';

    let currentStatus = item.status;
    if (item.agency_name || ['agency', 'acente', 'airbnb', 'booking', 'external_ical'].includes(item.source)) {
        currentStatus = 'agency';
    } else {
        currentStatus = 'booked';
    }

    const radioAgency = document.querySelector('#dashboardModal input[name="status"][value="agency"]');
    const radioOwner = document.querySelector('#dashboardModal input[name="status"][value="booked"]');
    const labelAgency = radioAgency ? radioAgency.closest('label') : null;
    const labelOwner = radioOwner ? radioOwner.closest('label') : null;

    if (labelAgency) labelAgency.classList.remove('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
    if (labelOwner) labelOwner.classList.remove('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
    if (radioAgency) radioAgency.disabled = false;
    if (radioOwner) radioOwner.disabled = false;

    const dateAgencyInput = document.getElementById('dashInputBookingDateAgency');
    const dateOwnerInput = document.getElementById('dashInputBookingDateOwner');
    if (dateAgencyInput) dateAgencyInput.disabled = true;
    if (dateOwnerInput) dateOwnerInput.disabled = true;

    if (currentStatus === 'booked') {
        if (radioOwner) radioOwner.checked = true;
        if (radioAgency) radioAgency.disabled = true;
        if (labelAgency) labelAgency.classList.add('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
    } else {
        if (radioAgency) radioAgency.checked = true;
        if (radioOwner) radioOwner.disabled = true;
        if (labelOwner) labelOwner.classList.add('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
    }

    toggleDashFields(currentStatus);

    if (item.source) {
        document.getElementById('dashInputSource').value = item.source;
    }

    let rawPhone = item.customer_phone ? String(item.customer_phone).trim() : '';
    let cleanPhone = rawPhone.replace(/[^0-9]/g, '');

    if (mode === 'edit') {
        // Kapatılan & Rezerve Tarihler alanından açıldığında sadece Güncelle butonu kalsın
        if (callBtn) callBtn.classList.add('hidden');
        if (waBtn) waBtn.classList.add('hidden');
    } else {
        // Bugün Giriş / Çıkış Yapacaklar alanından açıldığında Arama ve WhatsApp butonları gösterilsin
        if (cleanPhone) {
            if (callBtn) {
                callBtn.href = `tel:${rawPhone}`;
                callBtn.classList.remove('hidden');
            }
            let waPhone = cleanPhone.startsWith('0') ? '90' + cleanPhone.substring(1) : (cleanPhone.length === 10 ? '90' + cleanPhone : cleanPhone);
            if (waBtn) {
                const waText = `Merhaba ${item.customer_name || ''},\n${selectedVillaName} konutumuzla ilgili rezervasyon detayınız.`;
                waBtn.href = `https://wa.me/${waPhone}?text=${encodeURIComponent(waText)}`;
                waBtn.classList.remove('hidden');
            }
        } else {
            if (callBtn) callBtn.classList.add('hidden');
            if (waBtn) waBtn.classList.add('hidden');
        }
    }

    document.getElementById('dashboardModal').classList.remove('hidden');
}

function openDashboardModalForNew(startDate, endDate) {
    const dashModalButtons = document.getElementById('dashModalButtons');
    const submitBtn = document.getElementById('dashBtnSubmitForm');
    const callBtn = document.getElementById('dashCallModalBtn');
    const cancelBtn = document.getElementById('dashBtnCancelModal');
    const waBtn = document.getElementById('dashWhatsappBtn');

    if (dashModalButtons) dashModalButtons.classList.remove('hidden');
    if (submitBtn) submitBtn.classList.remove('hidden');
    if (callBtn) callBtn.classList.add('hidden');
    if (waBtn) waBtn.classList.add('hidden');
    if (cancelBtn) {
        cancelBtn.classList.remove('hidden');
        cancelBtn.innerText = 'İptal';
    }

    const radioAgency = document.querySelector('#dashboardModal input[name="status"][value="agency"]');
    const radioOwner = document.querySelector('#dashboardModal input[name="status"][value="booked"]');
    const labelAgency = radioAgency ? radioAgency.closest('label') : null;
    const labelOwner = radioOwner ? radioOwner.closest('label') : null;

    if (labelAgency) labelAgency.classList.remove('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
    if (labelOwner) labelOwner.classList.remove('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
    if (radioAgency) radioAgency.disabled = false;
    if (radioOwner) radioOwner.disabled = false;

    const dateAgencyInput = document.getElementById('dashInputBookingDateAgency');
    const dateOwnerInput = document.getElementById('dashInputBookingDateOwner');
    if (dateAgencyInput) dateAgencyInput.disabled = true;
    if (dateOwnerInput) dateOwnerInput.disabled = true;

    const selectEl = document.getElementById('dashboardVillaSelect');
    let selectedVillaName = '';
    if (selectEl && selectEl.options[selectEl.selectedIndex]) {
        selectedVillaName = selectEl.options[selectEl.selectedIndex].text;
    }
    document.getElementById('dashModalVillaName').innerText = selectedVillaName || '--';
    document.getElementById('dashModalTitle').innerText = 'Yeni Tarih Aralığı İşlemi';

    let targetVillaId = (currentDashboardVillaId && currentDashboardVillaId !== 'all') ? currentDashboardVillaId : '';
    if (!targetVillaId && selectEl && selectEl.value && selectEl.value !== 'all') {
        targetVillaId = selectEl.value;
    }
    if (!targetVillaId && selectEl && selectEl.options && selectEl.options.length > 1) {
        targetVillaId = selectEl.options[1].value;
    }

    document.getElementById('dashInputVillaId').value = targetVillaId;
    document.getElementById('dashInputStartDate').value = startDate;
    document.getElementById('dashInputEndDate').value = endDate;
    document.getElementById('dashInputReservationId').value = '';
    document.getElementById('dashSelectedDateText').innerText = `${formatDateTR(startDate)}  ➔  ${formatDateTR(endDate)}`;
    document.getElementById('dashModalError').classList.add('hidden');

    document.getElementById('dashInputCustomerName').value = '';
    document.getElementById('dashInputCustomerPhone').value = '';
    document.getElementById('dashInputAgencyName').value = '';
    setDashBookingDateValue(getTodayString());
    document.getElementById('dashInputPrice').value = '';
    document.getElementById('dashInputDownPayment').value = '';
    document.getElementById('dashInputRemainingPayment').value = '';
    document.getElementById('dashInputNote').value = '';

    const radio = document.querySelector(`#dashboardModal input[name="status"][value="agency"]`);
    if (radio) radio.checked = true;
    toggleDashFields('agency');

    document.getElementById('dashboardModal').classList.remove('hidden');
}

function closeDashboardModal() {
    document.getElementById('dashboardModal').classList.add('hidden');
}

function toggleDashFields(status) {
    const agencyNameBox = document.getElementById('dashAgencyNameBox');
    const customerAndPriceFields = document.getElementById('dashCustomerAndPriceFields');
    const sourceSelect = document.getElementById('dashInputSource');

    if (status === 'agency') {
        agencyNameBox.classList.remove('hidden');
        customerAndPriceFields.classList.add('hidden');
    } else {
        agencyNameBox.classList.add('hidden');
        customerAndPriceFields.classList.remove('hidden');
    }

    if (sourceSelect) {
        sourceSelect.innerHTML = `
            <option value="eski_misafir">Eski Misafir</option>
            <option value="instagram">İnstagram</option>
            <option value="google">Google</option>
            <option value="airbnb">Airbnb</option>
            <option value="vrbo">Vrbo</option>
            <option value="tavsiye">Tavsiye</option>
            <option value="acente">Acente</option>
        `;
    }
}

function handleDashboardReservationSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('dashReservationForm');
    const formData = new FormData(form);
    const status = formData.get('status');

    if (status === 'agency') {
        formData.set('source', 'acente');
    }

    const rawPrice = parseMoneyTR(document.getElementById('dashInputPrice').value);
    const rawDown = parseMoneyTR(document.getElementById('dashInputDownPayment').value);
    const rawRemaining = parseMoneyTR(document.getElementById('dashInputRemainingPayment').value);

    formData.set('price', rawPrice > 0 ? rawPrice : '');
    formData.set('down_payment', rawDown > 0 ? rawDown : '');
    formData.set('remaining_payment', rawRemaining > 0 ? rawRemaining : '');

    const targetVillaId = formData.get('villa_id') || document.getElementById('dashInputVillaId')?.value || (currentDashboardVillaId !== 'all' ? currentDashboardVillaId : '');

    if (!targetVillaId || targetVillaId === 'all') {
        const errDiv = document.getElementById('dashModalError');
        errDiv.innerText = 'Lütfen işlem yapmak için geçerli bir konut seçiniz.';
        errDiv.classList.remove('hidden');
        return;
    }

    fetch(`${appUrl}/api/villas/${targetVillaId}/reservations`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const rawPhone = formData.get('customer_phone') || document.getElementById('dashInputCustomerPhone')?.value || '';
            let phone = String(rawPhone).replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) {
                phone = '90' + phone.substring(1);
            } else if (phone.length === 10) {
                phone = '90' + phone;
            }

            const isEditing = !!(formData.get('reservation_id') || document.getElementById('dashInputReservationId')?.value);

            if (!isEditing && status === 'booked' && phone.length >= 10) {
                const vObj = getVillaObj(targetVillaId);
                const rawName = formData.get('customer_name') || document.getElementById('dashInputCustomerName')?.value || '';
                const customerName = String(rawName).trim() || 'Misafir';
                const villaName = vObj.name || document.getElementById('dashModalVillaName')?.innerText || 'Villa';
                const startDate = formatDateTR(formData.get('start_date') || '');
                const endDate = formatDateTR(formData.get('end_date') || '');
                const checkinTime = vObj.checkin_time || '';
                const checkoutTime = vObj.checkout_time || '';
                const priceVal = document.getElementById('dashInputPrice').value || '';
                const downVal = document.getElementById('dashInputDownPayment').value || '';
                const remainingVal = document.getElementById('dashInputRemainingPayment').value || '';
                const depositVal = vObj.deposit_amount || '';
                const poolHeatingVal = vObj.pool_heating_fee || '';

                let msg = `Sayın ${customerName}, rezervasyonunuz onaylanmıştır. Tüm detaylar aşağıda yer almaktadır.\n\n`;
                msg += `Konut Adı : ${villaName}\n`;
                msg += `Giriş Tarihi / Saati : ${startDate}${checkinTime ? ' / ' + checkinTime : ''}\n`;
                msg += `Çıkış Tarihi / Saati : ${endDate}${checkoutTime ? ' / ' + checkoutTime : ''}\n`;
                msg += `Toplam Ödeme : ${priceVal}\n`;
                msg += `Ön Ödeme : ${downVal}\n`;
                msg += `Kalan Ödeme : ${remainingVal}\n`;
                if (depositVal) msg += `Depozito : ${depositVal}\n`;
                if (poolHeatingVal) msg += `Havuz Isıtma Ücreti : ${poolHeatingVal}\n`;
                msg = msg.trim();

                const waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;

                try {
                    const win = window.open(waUrl, '_blank');
                    if (!win || win.closed || typeof win.closed === 'undefined') {
                        window.location.href = waUrl;
                    }
                } catch (e) {
                    window.location.href = waUrl;
                }
            }

            closeDashboardModal();
            refreshAllDashboardLists();
            if (popupCalendarInstance) {
                popupCalendarInstance.refetchEvents();
            }
        } else {
            const errDiv = document.getElementById('dashModalError');
            errDiv.innerText = data.message;
            errDiv.classList.remove('hidden');
        }
    })
    .catch(err => {
        console.error('Dashboard Reservation Error:', err);
        const errDiv = document.getElementById('dashModalError');
        errDiv.innerText = err.message || 'Bir ağ hatası oluştu.';
        errDiv.classList.remove('hidden');
    });
}

function quickDeleteDashboardReservation(id) {
    if (!confirm('Bu tarihi açmak / kaydı silmek istediğinize emin misiniz?')) return;
    fetch(`${appUrl}/api/reservations/${id}`, {
        method: 'DELETE'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            refreshAllDashboardLists();
            if (popupCalendarInstance) {
                popupCalendarInstance.refetchEvents();
            }
        } else {
            alert('İşlem gerçekleştirilemedi.');
        }
    });
}

let masterUpcomingReservations = [];

function loadUpcomingVillaReservations(villaId) {
    if (!villaId) return;

    let listToRender = masterDashboardReservations || [];
    if (villaId !== 'all') {
        listToRender = listToRender.filter(r => r.villa_id == villaId);
    }

    renderUpcomingReservations(listToRender);
}

function renderUpcomingReservations(events) {
    const listContainer = document.getElementById('upcomingReservationsList');
    if (!listContainer) return;

    const todayStr = getTodayString();
    const todayObj = new Date();
    todayObj.setHours(0, 0, 0, 0);

    const next7DaysObj = new Date(todayObj);
    next7DaysObj.setDate(next7DaysObj.getDate() + 7);
    const y = next7DaysObj.getFullYear();
    const m = String(next7DaysObj.getMonth() + 1).padStart(2, '0');
    const d = String(next7DaysObj.getDate()).padStart(2, '0');
    const next7DaysStr = `${y}-${m}-${d}`;

    const upcoming = events.filter(item => {
        if (!item.start_date) return false;
        return item.start_date >= todayStr && item.start_date <= next7DaysStr;
    });

    upcoming.sort((a, b) => a.start_date.localeCompare(b.start_date));

    if (upcoming.length === 0) {
        listContainer.innerHTML = `
            <div class="col-span-2 text-center py-8 text-slate-400 text-xs">
                <i class="fa-regular fa-calendar-check text-2xl text-slate-300 mb-1"></i>
                <p>Önümüzdeki 7 gün (1 hafta) içinde gerçekleşecek yaklaşan giriş kaydı bulunmuyor.</p>
            </div>
        `;
        return;
    }

    let html = '';
    upcoming.forEach(item => {
        let badgeClass = 'bg-slate-50 text-slate-800 border-slate-200';
        let icon = 'fa-lock text-slate-600';

        if (item.status === 'booked') {
            badgeClass = 'bg-rose-50 text-rose-800 border-rose-200';
            icon = 'fa-circle-check text-rose-500';
        } else if (item.status === 'optional') {
            badgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
            icon = 'fa-clock text-amber-500';
        }

        const startTr = formatDateTR(item.start_date);
        const endTr = formatDateTR(item.end_date);
        
        let labelText = 'Ev Sahibi';
        if (item.status === 'blocked') {
            labelText = 'Kapalı / Bloke';
        } else if (item.status === 'optional') {
            labelText = 'Opsiyonlu';
        } else if (item.agency_name || ['agency', 'acente', 'airbnb', 'booking', 'external_ical'].includes(item.source)) {
            const agencyTitle = item.agency_name || (item.customer_name && item.source === 'acente' ? item.customer_name : '');
            labelText = agencyTitle ? `Acente: ${agencyTitle}` : 'Acente';
        } else {
            labelText = item.customer_name ? `Müşteri: ${item.customer_name}` : 'Ev Sahibi';
        }

        const villaLabel = item.villa_name ? `<span class="text-indigo-700 font-bold ml-1">(${item.villa_name})</span>` : '';

        html += `
            <div class="p-3.5 rounded-xl border ${badgeClass} text-xs flex items-center justify-between gap-2 transition hover:shadow-sm">
                <div class="min-w-0 flex-1">
                    <div class="font-bold flex items-center space-x-1.5 text-slate-900 truncate">
                        <i class="fa-solid ${icon} text-[11px] flex-shrink-0"></i>
                        <span class="truncate">${startTr} ➔ ${endTr}</span>
                    </div>
                    <div class="text-[11px] font-semibold mt-0.5 text-slate-600 truncate">${labelText} ${villaLabel}</div>
                    ${item.customer_phone ? `<div class="text-[10px] text-slate-500 mt-0.5"><i class="fa-solid fa-phone text-[9px] mr-1"></i>${item.customer_phone}</div>` : ''}
                </div>
                <div class="flex items-center space-x-1 flex-shrink-0">
                    <button onclick="openModalForDashboardReservation(${item.id}, 'today')" class="px-2.5 py-1 bg-white hover:bg-indigo-600 hover:text-white text-indigo-700 font-bold rounded-lg border border-slate-200 text-[11px] shadow-sm transition" title="Rezervasyon Detayı">
                        Detay
                    </button>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
}

function refreshAllDashboardLists() {
    const selectEl = document.getElementById('dashboardVillaSelect');
    if (selectEl && selectEl.value) {
        currentDashboardVillaId = selectEl.value;
    }

    fetch(`${appUrl}/api/dashboard/all-reservations`)
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data)) {
                masterDashboardReservations = data;
                for (let key in dashboardReservationsCache) {
                    delete dashboardReservationsCache[key];
                }
                data.forEach(r => {
                    if (r && r.villa_id) {
                        if (!dashboardReservationsCache[r.villa_id]) dashboardReservationsCache[r.villa_id] = [];
                        dashboardReservationsCache[r.villa_id].push(r);
                    }
                });
            }
            loadDashboardVillaReservations(currentDashboardVillaId);
            const upcomingSelectEl = document.getElementById('upcomingVillaSelect');
            if (upcomingSelectEl) {
                loadUpcomingVillaReservations(upcomingSelectEl.value);
            }
        })
        .catch(() => {
            loadDashboardVillaReservations(currentDashboardVillaId);
            const upcomingSelectEl = document.getElementById('upcomingVillaSelect');
            if (upcomingSelectEl) {
                loadUpcomingVillaReservations(upcomingSelectEl.value);
            }
        });
}

document.addEventListener('DOMContentLoaded', function() {
    // Populate cache instantly from pre-fetched masterDashboardReservations in 0ms
    if (Array.isArray(masterDashboardReservations)) {
        masterDashboardReservations.forEach(r => {
            if (r && r.villa_id) {
                if (!dashboardReservationsCache[r.villa_id]) {
                    dashboardReservationsCache[r.villa_id] = [];
                }
                dashboardReservationsCache[r.villa_id].push(r);
            }
        });
    }

    const selectEl = document.getElementById('dashboardVillaSelect');
    if (selectEl && selectEl.value) {
        loadDashboardVillaReservations(selectEl.value);
    }

    const upcomingSelectEl = document.getElementById('upcomingVillaSelect');
    if (upcomingSelectEl) {
        loadUpcomingVillaReservations(upcomingSelectEl.value);
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
