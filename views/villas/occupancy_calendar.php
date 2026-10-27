<?php
use App\Helpers;
use App\Auth;
$title = 'Doluluk Takvimi - TakvimTakip';
require __DIR__ . '/../layouts/header.php';

// Month names in Turkish
$monthsTR = [
    1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
    5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
    9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
];

// Day initials in Turkish (Mon..Sun -> Pt, Sa, Ça, Pe, Cu, Ct, Pz)
$daysShortTR = ['Pz', 'Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct'];
$daysSingleTR = ['P', 'P', 'S', 'Ç', 'P', 'C', 'C'];

$month = (int)($month ?? date('m'));
$year = (int)($year ?? date('Y'));
$daysInMonth = (int)($daysInMonth ?? date('t', strtotime(sprintf('%04d-%02d-01', $year, $month))));

$monthName = $monthsTR[$month] ?? '';

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
#ganttMatrixContainer {
    min-height: 140px;
    max-height: 520px;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
}
@media (min-width: 1024px) {
    #ganttMatrixContainer {
        min-height: 264px;
        max-height: 264px;
    }
}
</style>

<div class="max-w-[1440px] w-full max-w-full mx-auto space-y-5 overflow-x-hidden">
    
    <!-- Top Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/90 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-indigo-600 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-chart-gantt"></i>
                <span>Doluluk Çizelgesi & Zaman Takvimi</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Doluluk Takvimi</h1>
            <p class="text-slate-500 text-sm mt-0.5">Tüm konutlarınızın aylık doluluk ve müsaitlik çizelgesini tek bir ekranda inceleyin.</p>
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

    <!-- Navigation Bar & Controls Toolbar (Centered Month Selection) -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-sm flex items-center justify-center">
        <div class="flex items-center space-x-4">
            <button type="button" onclick="navigateMonth(-1)" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-indigo-600 hover:text-white text-slate-700 flex items-center justify-center font-bold text-sm transition shadow-xs active:scale-95" title="Önceki Ay">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            
            <div id="matrixMonthTitle" class="text-lg font-bold text-slate-800 min-w-[150px] text-center select-none">
                <?= $monthName ?> <?= $year ?>
            </div>

            <button type="button" onclick="navigateMonth(1)" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-indigo-600 hover:text-white text-slate-700 flex items-center justify-center font-bold text-sm transition shadow-xs active:scale-95" title="Sonraki Ay">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- Status Color Legend Bar -->
    <div class="bg-white px-4 py-2.5 rounded-2xl shadow-sm border border-slate-200/90 flex flex-wrap items-center justify-around gap-2 text-xs">
        <div class="flex items-center space-x-1.5">
            <span class="w-3.5 h-3.5 rounded-md bg-indigo-600 inline-block shadow-2xs"></span>
            <span class="font-medium text-slate-700">Acente<span class="hidden sm:inline"> Rezervasyonu</span></span>
        </div>
        <div class="flex items-center space-x-1.5">
            <span class="w-3.5 h-3.5 rounded-md bg-[#fd5a5f] inline-block shadow-2xs"></span>
            <span class="font-medium text-slate-700">Ev Sahibi<span class="hidden sm:inline"> Rezervasyonu</span></span>
        </div>
        <div class="flex items-center space-x-1.5">
            <span class="w-3.5 h-3.5 rounded-md bg-white border border-slate-300 inline-block shadow-2xs"></span>
            <span class="font-medium text-slate-700">Müsait</span>
        </div>
    </div>

    <!-- Empty Villa State -->
    <?php if (empty($villas)): ?>
        <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm max-w-lg mx-auto">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="fa-solid fa-house-chimney"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Henüz Konut Bulunmuyor</h3>
            <p class="text-slate-500 text-xs mb-4">Doluluk çizelgesinde görüntülenecek aktif bir konut bulunmamaktadır.</p>
            <a href="<?= APP_URL ?>/villas/new" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md transition space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Yeni Konut Ekle</span>
            </a>
        </div>
    <?php else: ?>

        <!-- GANTT MATRIX UNIFIED STICKY ARCHITECTURE (Displays 5 Villa Rows = 264px Max Height on Desktop, 55vh on Mobile) -->
        <div id="ganttMatrixContainer" class="w-full relative overflow-auto min-h-[140px] max-h-[520px] lg:max-h-[264px] rounded-2xl border border-slate-200/90 shadow-sm bg-white select-none" style="min-height: 140px; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
            <table class="border-separate border-spacing-0 text-left text-xs w-full" style="min-width: <?= 160 + ($daysInMonth * 44) ?>px; width: <?= 160 + ($daysInMonth * 44) ?>px;">
                <thead>
                    <tr class="h-[44px]">
                        <!-- STICKY TOP-LEFT CORNER HEADER (z-40) -->
                        <th class="sticky top-0 left-0 z-40 bg-slate-50 border-r border-b border-slate-300 px-3 h-[44px] min-w-[160px] max-w-[160px] w-[160px] font-bold text-slate-800 text-xs shadow-2xs select-none align-middle">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center space-x-1.5">
                                    <i class="fa-solid fa-house text-indigo-600"></i>
                                    <span>Villalar</span>
                                </span>
                                <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded-full bg-slate-200/80 text-slate-700 shrink-0">
                                    <?= count($villas) ?> Konut
                                </span>
                            </div>
                        </th>

                        <!-- STICKY DAY HEADERS (z-30) -->
                        <?php 
                        $todayStr = date('Y-m-d');
                        for ($d = 1; $d <= $daysInMonth; $d++): 
                            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                            $dayOfWeekNum = (int)date('w', strtotime($dateStr));
                            $isWeekend = ($dayOfWeekNum === 0 || $dayOfWeekNum === 6);
                            $isToday = ($dateStr === $todayStr);
                            $dayLetter = $daysShortTR[$dayOfWeekNum] ?? '';
                        ?>
                            <th class="sticky top-0 z-30 w-11 min-w-[44px] max-w-[44px] p-1.5 text-center border-r border-b border-slate-200 font-bold <?= $isToday ? 'bg-indigo-100 text-indigo-950 border-b-2 border-b-indigo-600' : ($isWeekend ? 'bg-amber-50 text-indigo-950' : 'bg-slate-50 text-slate-700') ?>">
                                <div class="text-[12px] leading-tight font-extrabold"><?= $d ?></div>
                                <div class="text-[10px] font-semibold uppercase tracking-tighter <?= $isWeekend ? 'text-indigo-600 font-bold' : 'text-slate-600' ?>"><?= $dayLetter ?></div>
                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody id="matrixTbody">
                    <?php foreach ($villas as $v): ?>
                        <?php 
                        $vName = Helpers::sanitize($v['name']);
                        $vCity = Helpers::sanitize($v['city'] ?? '');
                        $vDistrict = Helpers::sanitize($v['district'] ?? '');
                        $locationStr = trim($vCity . ($vDistrict ? ' / ' . $vDistrict : ''));
                        ?>
                        <tr class="villa-row hover:bg-slate-50/50 transition group h-[44px]"
                            data-id="<?= $v['id'] ?>"
                            data-name="<?= $vName ?>"
                            data-location="<?= $locationStr ?>">

                            <!-- STICKY LEFT VILLA NAME CELL (z-20) -->
                            <td class="sticky left-0 z-20 bg-white group-hover:bg-slate-50 border-r border-b border-slate-300 px-3 h-[44px] min-h-[44px] max-h-[44px] min-w-[160px] max-w-[160px] w-[160px] align-middle shadow-2xs">
                                <a href="<?= APP_URL ?>/villas/<?= $v['id'] ?>/calendar" class="font-bold text-xs text-slate-800 hover:text-indigo-600 transition truncate block" title="<?= $vName ?>">
                                    <?= $vName ?>
                                </a>
                            </td>

                            <!-- TIMELINE MATRIX CELL -->
                            <td colspan="<?= $daysInMonth ?>" class="p-0 relative h-[44px] min-h-[44px] max-h-[44px] bg-white align-middle border-b border-slate-200">
                                
                                <!-- Background Column Dividers Grid -->
                                <div class="absolute inset-0 flex pointer-events-none">
                                    <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                        <?php 
                                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                        $dayOfWeekNum = (int)date('w', strtotime($dateStr));
                                        $isWeekend = ($dayOfWeekNum === 0 || $dayOfWeekNum === 6);
                                        $isToday = ($dateStr === $todayStr);
                                        ?>
                                        <div class="w-11 min-w-[44px] max-w-[44px] h-full border-r border-slate-200 <?= $isToday ? 'bg-indigo-50/30' : ($isWeekend ? 'bg-slate-50/40' : '') ?>"></div>
                                    <?php endfor; ?>
                                </div>

                                <!-- OVERLAY RESERVATION BARS -->
                                <?php 
                                $colWidth = 44; // 44px matching day header column width
                                $monthStartStr = sprintf('%04d-%02d-01', $year, $month);
                                $monthEndStr = date('Y-m-t', strtotime($monthStartStr));

                                foreach ($reservations as $res): 
                                    if ((int)$res['villa_id'] !== (int)$v['id']) continue;

                                    $resStart = $res['start_date'];
                                    $resEnd = $res['end_date'];

                                    // Clamp dates to current month range
                                    $hasPrevOverflow = ($resStart < $monthStartStr);
                                    $hasNextOverflow = ($resEnd > $monthEndStr);

                                    // Calculate Day numbers (1..daysInMonth)
                                    if ($hasPrevOverflow) {
                                        $startDay = 1;
                                    } else {
                                        $startDay = (int)date('j', strtotime($resStart));
                                    }

                                    if ($hasNextOverflow) {
                                        $endDay = $daysInMonth + 1;
                                    } else {
                                        $endDay = (int)date('j', strtotime($resEnd));
                                    }

                                    // Minimum span of 1 day cell
                                    if ($endDay <= $startDay) {
                                        $endDay = $startDay + 1;
                                    }

                                    $leftPx = ($startDay - 1) * $colWidth;

                                    // 45-degree diagonal clip-path calculations for check-in & check-out edges
                                    $hasLeftCut = !$hasPrevOverflow;
                                    $hasRightCut = !$hasNextOverflow;
                                    $slant = (int)($colWidth / 2); // 22px diagonal slant (half day cell)

                                    if ($hasRightCut) {
                                        // Extends into check-out day by half cell width (top-left of check-out day filled)
                                        $widthPx = ($endDay - $startDay + 0.5) * $colWidth;
                                        $topLeft = $hasLeftCut ? "{$slant}px 0%" : "0% 0%";
                                        $topRight = "100% 0%";
                                        $bottomRight = "calc(100% - {$slant}px) 100%";
                                        $bottomLeft = "0% 100%";
                                    } else {
                                        // Extends to month boundary
                                        $widthPx = ($endDay - $startDay) * $colWidth;
                                        $topLeft = $hasLeftCut ? "{$slant}px 0%" : "0% 0%";
                                        $topRight = "100% 0%";
                                        $bottomRight = "100% 100%";
                                        $bottomLeft = "0% 100%";
                                    }

                                    $clipStyle = "clip-path: polygon({$topLeft}, {$topRight}, {$bottomRight}, {$bottomLeft});";

                                    // Determine Color & Category
                                    $statusCategory = 'booked';
                                    $badgeBgClass = 'bg-[#fd5a5f] hover:bg-rose-600 text-white';
                                    $labelTitle = 'Ev Sahibi Rezervasyonu';

                                    if (($res['status'] ?? '') === 'blocked') {
                                        $statusCategory = 'blocked';
                                        $badgeBgClass = 'bg-slate-700 hover:bg-slate-800 text-white';
                                        $labelTitle = 'Kapalı / Bloke';
                                    } elseif (($res['status'] ?? '') === 'optional') {
                                        $statusCategory = 'optional';
                                        $badgeBgClass = 'bg-amber-500 hover:bg-amber-600 text-slate-900 font-extrabold';
                                        $labelTitle = 'Opsiyonlu';
                                    } elseif (($res['source'] ?? '') === 'acente' || !empty($res['agency_name'])) {
                                        $statusCategory = 'agency';
                                        $badgeBgClass = 'bg-indigo-600 hover:bg-indigo-700 text-white';
                                        $labelTitle = 'Acente Rezervasyonu';
                                    }

                                    // Label text inside bar
                                    $displayText = !empty($res['agency_name']) ? ('Acente: ' . Helpers::sanitize($res['agency_name'])) : 
                                                  (!empty($res['customer_name']) ? Helpers::sanitize($res['customer_name']) : $labelTitle);
                                ?>
                                    <div class="res-bar absolute top-1.5 bottom-1.5 flex items-center text-[11px] font-bold shadow-2xs transition transform hover:scale-[1.01] hover:z-20 cursor-pointer overflow-hidden truncate <?= $badgeBgClass ?>"
                                         style="left: <?= $leftPx ?>px; width: <?= $widthPx ?>px; <?= $clipStyle ?> padding-left: <?= $hasLeftCut ? 16 : 8 ?>px; padding-right: <?= $hasRightCut ? 16 : 8 ?>px;"
                                         data-category="<?= $statusCategory ?>"
                                         data-id="<?= $res['id'] ?>"
                                         data-villa="<?= $vName ?>"
                                         data-title="<?= Helpers::sanitize($labelTitle) ?>"
                                         data-agency="<?= Helpers::sanitize($res['agency_name'] ?? '') ?>"
                                         data-customer="<?= Helpers::sanitize($res['customer_name'] ?? '') ?>"
                                         data-phone="<?= Helpers::sanitize($res['customer_phone'] ?? '') ?>"
                                         data-start="<?= date('d.m.Y', strtotime($res['start_date'])) ?>"
                                         data-end="<?= date('d.m.Y', strtotime($res['end_date'])) ?>"
                                         data-price="<?= !empty($res['price']) ? Helpers::formatMoney($res['price']) : '' ?>"
                                         data-note="<?= Helpers::sanitize($res['note'] ?? '') ?>"
                                         onclick="openResModal(this)">
                                        
                                        <?php if ($hasLeftCut): ?>
                                            <svg class="absolute top-0 left-0 h-full w-[22px] pointer-events-none z-10" preserveAspectRatio="none" viewBox="0 0 22 36">
                                                <line x1="22" y1="0" x2="0" y2="36" stroke="#ffffff" stroke-width="2.5" />
                                            </svg>
                                        <?php endif; ?>

                                        <?php if ($hasRightCut): ?>
                                            <svg class="absolute top-0 right-0 h-full w-[22px] pointer-events-none z-10" preserveAspectRatio="none" viewBox="0 0 22 36">
                                                <line x1="22" y1="0" x2="0" y2="36" stroke="#ffffff" stroke-width="2.5" />
                                            </svg>
                                        <?php endif; ?>

                                        <?php if ($hasPrevOverflow): ?>
                                            <i class="fa-solid fa-caret-left mr-1 text-[10px] shrink-0 opacity-80"></i>
                                        <?php endif; ?>

                                        <span class="truncate min-w-0"><?= $displayText ?></span>

                                        <?php if ($hasNextOverflow): ?>
                                            <i class="fa-solid fa-caret-right ml-1 text-[10px] shrink-0 opacity-80"></i>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

<!-- RESERVATION DETAIL POPUP MODAL -->
<div id="resDetailModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 border border-slate-100 space-y-4 transform transition-all scale-95 opacity-0 duration-200" id="resModalCard">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <span id="modalStatusBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 mb-1">
                    Rezervasyon Detayı
                </span>
                <h3 id="modalVillaName" class="text-lg font-bold text-slate-800">--</h3>
            </div>
            <button type="button" onclick="closeResModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center text-sm font-bold">&times;</button>
        </div>

        <div class="space-y-3 text-xs">
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80 flex items-center justify-between">
                <div>
                    <div class="text-slate-400 text-[10px] font-bold uppercase">Giriş Tarihi</div>
                    <div id="modalStartDate" class="font-bold text-slate-800 text-sm mt-0.5">--</div>
                </div>
                <i class="fa-solid fa-arrow-right text-slate-300 text-sm"></i>
                <div class="text-right">
                    <div class="text-slate-400 text-[10px] font-bold uppercase">Çıkış Tarihi</div>
                    <div id="modalEndDate" class="font-bold text-slate-800 text-sm mt-0.5">--</div>
                </div>
            </div>

            <div id="modalCustomerBox" class="p-3 bg-indigo-50/60 rounded-xl border border-indigo-100 space-y-1">
                <div class="text-[10px] font-bold text-indigo-600 uppercase">Müşteri / Acente</div>
                <div id="modalCustomerName" class="font-bold text-slate-800 text-sm">--</div>
                <div id="modalCustomerPhone" class="text-slate-500 font-semibold text-xs"></div>
            </div>

            <div id="modalPriceBox" class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100 flex items-center justify-between">
                <span class="text-emerald-700 font-bold">Toplam Konaklama Ücreti</span>
                <span id="modalPrice" class="font-extrabold text-emerald-800 text-sm">--</span>
            </div>

            <div id="modalNoteBox" class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 hidden">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Not / Açıklama</div>
                <div id="modalNote" class="text-slate-700 font-medium mt-0.5"></div>
            </div>
        </div>

        <div class="pt-2 flex items-center space-x-2">
            <a id="modalManageBtn" href="#" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs text-center transition shadow-sm flex items-center justify-center space-x-2">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Takvimi Yönet</span>
            </a>
        </div>
    </div>
</div>

<script>
let currentYear = <?= $year ?>;
let currentMonth = <?= $month ?>;
let activeFilter = 'all';

function navigateMonth(delta) {
    currentMonth += delta;
    if (currentMonth > 12) {
        currentMonth = 1;
        currentYear++;
    } else if (currentMonth < 1) {
        currentMonth = 12;
        currentYear--;
    }
    updatePageLocation();
}

function goToCurrentMonth() {
    const today = new Date();
    currentYear = today.getFullYear();
    currentMonth = today.getMonth() + 1;
    updatePageLocation();
}

function jumpToMonth() {
    const mSelect = document.getElementById('matrixMonthSelect');
    const ySelect = document.getElementById('matrixYearSelect');
    if (mSelect) currentMonth = parseInt(mSelect.value) || currentMonth;
    if (ySelect) currentYear = parseInt(ySelect.value) || currentYear;
    updatePageLocation();
}

function updatePageLocation() {
    window.location.href = `<?= APP_URL ?>/occupancy-calendar?year=${currentYear}&month=${currentMonth}`;
}

// Filter Tabs (Tümü, Acente, Ev Sahibi, Kapalı, Opsiyonlu)
function setMatrixFilter(category, btnEl) {
    activeFilter = category;
    
    document.querySelectorAll('.matrix-filter-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'text-slate-800', 'shadow-xs');
        btn.classList.add('text-slate-600');
    });

    if (btnEl) {
        btnEl.classList.remove('text-slate-600');
        btnEl.classList.add('bg-white', 'text-slate-800', 'shadow-xs');
    }

    document.querySelectorAll('.res-bar').forEach(bar => {
        const cat = bar.getAttribute('data-category');
        if (category === 'all' || cat === category) {
            bar.style.display = 'flex';
        } else {
            bar.style.display = 'none';
        }
    });
}

// Villa Search Input
function filterMatrixVillas() {
    const input = document.getElementById('matrixSearchInput');
    const clearBtn = document.getElementById('clearMatrixSearchBtn');
    const query = input ? input.value.trim().toLowerCase() : '';

    if (clearBtn) {
        if (query) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    document.querySelectorAll('.villa-row').forEach(row => {
        const name = (row.getAttribute('data-name') || '').toLowerCase();
        const location = (row.getAttribute('data-location') || '').toLowerCase();

        if (!query || name.includes(query) || location.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function clearMatrixSearch() {
    const input = document.getElementById('matrixSearchInput');
    if (input) input.value = '';
    filterMatrixVillas();
}

// Modal Popup
function openResModal(el) {
    const modal = document.getElementById('resDetailModal');
    const card = document.getElementById('resModalCard');
    if (!modal || !card) return;

    const villa = el.getAttribute('data-villa') || '';
    const title = el.getAttribute('data-title') || 'Rezervasyon';
    const agency = el.getAttribute('data-agency') || '';
    const customer = el.getAttribute('data-customer') || '';
    const phone = el.getAttribute('data-phone') || '';
    const start = el.getAttribute('data-start') || '';
    const end = el.getAttribute('data-end') || '';
    const price = el.getAttribute('data-price') || '';
    const note = el.getAttribute('data-note') || '';

    document.getElementById('modalVillaName').textContent = villa;
    document.getElementById('modalStatusBadge').textContent = title;
    document.getElementById('modalStartDate').textContent = start;
    document.getElementById('modalEndDate').textContent = end;

    const custNameEl = document.getElementById('modalCustomerName');
    const custPhoneEl = document.getElementById('modalCustomerPhone');
    
    let displayCust = agency ? (`Acente: ${agency}`) : (customer || 'Belirtilmedi');
    custNameEl.textContent = displayCust;
    custPhoneEl.textContent = phone ? (`İletişim: ${phone}`) : '';

    const priceBox = document.getElementById('modalPriceBox');
    const priceEl = document.getElementById('modalPrice');
    if (price) {
        priceBox.classList.remove('hidden');
        priceEl.textContent = price;
    } else {
        priceBox.classList.add('hidden');
    }

    const noteBox = document.getElementById('modalNoteBox');
    const noteEl = document.getElementById('modalNote');
    if (note) {
        noteBox.classList.remove('hidden');
        noteEl.textContent = note;
    } else {
        noteBox.classList.add('hidden');
    }

    modal.classList.remove('hidden');
    setTimeout(() => {
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeResModal() {
    const modal = document.getElementById('resDetailModal');
    const card = document.getElementById('resModalCard');
    if (!modal || !card) return;

    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

document.getElementById('resDetailModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeResModal();
});

// Touch Engine v2: fully manual, single-axis scrolling.
// WHY THE OLD VERSION DRIFTED ON iOS ONLY:
// The container had `overflow-auto` on both axes, so iOS Safari's native
// scrolling/momentum for that element is decided and driven by WebKit's
// compositor thread, independently of (and slightly ahead of) our touchmove
// handler. Re-zeroing scrollTop/scrollLeft from JS on every event could not
// reliably win that race, so the "locked" axis kept creeping. Android's
// Chromium doesn't have that same async race, which is why it looked fine there.
// FIX: set `touch-action: none` on the container (see the div above) so the
// browser never starts its own scroll/momentum for it at all, and drive both
// scrollTop and scrollLeft ourselves. With nothing native left to fight,
// there is no longer anything that can drift.
(function() {
    const container = document.getElementById('ganttMatrixContainer');
    if (!container) return;

    // Android native SwipeRefresh kilidini kapat
    try {
        if (window.AndroidBridge && typeof window.AndroidBridge.setSwipeRefreshEnabled === 'function') {
            window.AndroidBridge.setSwipeRefreshEnabled(false);
        }
    } catch (e) {}

    let startX = 0, startY = 0;
    let lastX = 0, lastY = 0;
    let lastTime = 0;
    let velocityX = 0, velocityY = 0;
    let lockedAxis = null; // 'horizontal' | 'vertical' | null
    let rafId = null;

    function stopMomentum() {
        if (rafId) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }
    }

    function clampScroll() {
        const maxTop = container.scrollHeight - container.clientHeight;
        const maxLeft = container.scrollWidth - container.clientWidth;
        if (container.scrollTop < 0) container.scrollTop = 0;
        else if (container.scrollTop > maxTop) container.scrollTop = maxTop;
        if (container.scrollLeft < 0) container.scrollLeft = 0;
        else if (container.scrollLeft > maxLeft) container.scrollLeft = maxLeft;
    }

    container.addEventListener('touchstart', function(e) {
        if (!e.touches || e.touches.length === 0) return;
        try {
            if (window.AndroidBridge && typeof window.AndroidBridge.setSwipeRefreshEnabled === 'function') {
                window.AndroidBridge.setSwipeRefreshEnabled(false);
            }
        } catch (err) {}
        stopMomentum();
        lockedAxis = null;
        startX = lastX = e.touches[0].clientX;
        startY = lastY = e.touches[0].clientY;
        lastTime = Date.now();
        velocityX = 0;
        velocityY = 0;
    }, { passive: true });

    container.addEventListener('touchmove', function(e) {
        if (!e.touches || e.touches.length === 0) return;

        const currentX = e.touches[0].clientX;
        const currentY = e.touches[0].clientY;

        // Per-frame deltas: what we actually apply to the scroll position.
        const stepX = currentX - lastX;
        const stepY = currentY - lastY;

        // Cumulative deltas since touchstart: used only to decide the axis once.
        const totalX = currentX - startX;
        const totalY = currentY - startY;

        if (!lockedAxis && (Math.abs(totalX) > 4 || Math.abs(totalY) > 4)) {
            lockedAxis = (Math.abs(totalX) > Math.abs(totalY)) ? 'horizontal' : 'vertical';
        }

        if (lockedAxis === 'vertical') {
            const maxTop = container.scrollHeight - container.clientHeight;
            if (maxTop > 2) {
                const isScrollingDown = stepY < 0;
                const isScrollingUp = stepY > 0;
                const canScrollDown = container.scrollTop < (maxTop - 1);
                const canScrollUp = container.scrollTop > 1;

                if ((isScrollingDown && canScrollDown) || (isScrollingUp && canScrollUp)) {
                    e.preventDefault();
                    container.scrollTop -= stepY;
                    clampScroll();
                }
            }
        } else if (lockedAxis === 'horizontal') {
            e.preventDefault();
            container.scrollLeft -= stepX;
            clampScroll();
        }

        if (lockedAxis) {
            const now = Date.now();
            const dt = Math.max(now - lastTime, 1);
            velocityX = stepX / dt;
            velocityY = stepY / dt;
            lastTime = now;
        }

        lastX = currentX;
        lastY = currentY;
    }, { passive: false });

    function runMomentum() {
        const friction = 0.94;
        velocityX *= friction;
        velocityY *= friction;

        if (lockedAxis === 'vertical') {
            container.scrollTop -= velocityY * 16;
        } else if (lockedAxis === 'horizontal') {
            container.scrollLeft -= velocityX * 16;
        }
        clampScroll();

        if (Math.abs(velocityX) > 0.02 || Math.abs(velocityY) > 0.02) {
            rafId = requestAnimationFrame(runMomentum);
        } else {
            rafId = null;
            lockedAxis = null;
        }
    }

    function endTouch() {
        // Give the release a light momentum/inertia so it still feels
        // native, since touch-action: none disabled iOS's own momentum.
        if (lockedAxis && (Math.abs(velocityX) > 0.02 || Math.abs(velocityY) > 0.02)) {
            runMomentum();
        } else {
            lockedAxis = null;
        }
    }

    container.addEventListener('touchend', endTouch, { passive: true });
    container.addEventListener('touchcancel', function() {
        stopMomentum();
        lockedAxis = null;
    }, { passive: true });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
