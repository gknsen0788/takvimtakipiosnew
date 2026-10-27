<?php
use App\Helpers;
use App\Auth;
$title = 'Takvim Yönetimi - TakvimTakip';

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

require __DIR__ . '/../layouts/header.php';
?>

<style>
@keyframes proShimmer {
    0% { transform: translateX(-100%) skewX(-12deg); }
    100% { transform: translateX(250%) skewX(-12deg); }
}
</style>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/90 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-indigo-600 uppercase tracking-wider mb-1">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Takvim & Müsaitlik Yönetimi</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Takvim Yönetimi</h1>
            <p class="text-slate-500 text-sm mt-0.5">Tarih kapatmak, opsiyon koymak veya müşteri rezervasyonu eklemek istediğiniz konutu seçin.</p>
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

    <?php if (!empty($villas)): ?>
        <!-- Search Bar (Çerçevenin Dışında %100 Genişlik) -->
        <div class="w-full relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <input type="text" id="calSelectSearchInput" oninput="filterCalendarVillas()" placeholder="Konut adı veya konum ara..." class="w-full pl-10 pr-10 py-3 rounded-2xl border border-slate-200/90 text-sm font-medium outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-xs placeholder-slate-400">
            <button id="clearCalSelectSearchBtn" onclick="clearCalSelectSearch()" class="hidden absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (empty($villas)): ?>
        <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm max-w-lg mx-auto">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="fa-solid fa-house-chimney"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Henüz Konut Bulunmuyor</h3>
            <p class="text-slate-500 text-xs mb-4">Takvimini yönetebileceğiniz ekli bir konut bulunmamaktadır.</p>
            <a href="<?= APP_URL ?>/villas/new" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md transition space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Yeni Konut Ekle</span>
            </a>
        </div>
    <?php else: ?>
        <!-- No Search Result Banner -->
        <div id="noCalVillaResult" class="hidden bg-white rounded-2xl p-8 text-center border border-slate-200 shadow-sm max-w-md mx-auto">
            <i class="fa-solid fa-magnifying-glass text-3xl text-slate-300 mb-2"></i>
            <h3 class="text-sm font-bold text-slate-800 mb-1">Aramaya Uygun Konut Bulunamadı</h3>
            <p class="text-slate-500 text-xs mb-3">Aradığınız kriterlere uyan konut bulunamadı.</p>
            <button onclick="clearCalSelectSearch()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition">Aramayı Temizle</button>
        </div>

        <!-- Villa Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($villas as $v): ?>
                <div class="cal-villa-card bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition flex flex-col overflow-hidden group"
                     data-id="<?= $v['id'] ?>"
                     data-name="<?= Helpers::sanitize($v['name']) ?>"
                     data-location="<?= Helpers::sanitize($v['city']) ?> <?= Helpers::sanitize($v['district']) ?>">
                    
                    <!-- Cover Image Header -->
                    <a href="<?= APP_URL ?>/villas/<?= $v['id'] ?>/calendar" class="h-48 bg-slate-100 relative block overflow-hidden">
                        <?php if (!empty($v['cover_image'])): ?>
                            <img src="<?= Helpers::sanitize($v['cover_image']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <?php else: ?>
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                                <i class="fa-solid fa-image text-4xl mb-1"></i>
                                <span class="text-xs">Fotoğraf Yok</span>
                            </div>
                        <?php endif; ?>

                        <!-- Property Type Badge -->
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold shadow-sm bg-indigo-600/90 text-white backdrop-blur-sm">
                                <i class="fa-solid fa-house text-[10px] mr-1"></i><?= Helpers::sanitize($v['property_type'] ?? 'Villa') ?>
                            </span>
                        </div>

                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold shadow-sm <?= $v['status'] === 'active' ? 'bg-emerald-500 text-white' : 'bg-slate-500 text-white' ?>">
                                <?= $v['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                            </span>
                        </div>

                        <!-- Admin Owner Badge -->
                        <?php if (Auth::isAdmin() && !empty($v['owner_name'])): ?>
                            <div class="absolute bottom-3 left-3">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold shadow-sm bg-slate-900/80 text-amber-300 backdrop-blur-sm">
                                    <i class="fa-solid fa-user-gear text-[10px] mr-1"></i>Ev Sahibi: <?= Helpers::sanitize($v['owner_name']) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </a>

                    <!-- Card Body -->
                    <div class="p-5 flex-grow flex flex-col justify-between space-y-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800 line-clamp-1 group-hover:text-indigo-600 transition">
                                <a href="<?= APP_URL ?>/villas/<?= $v['id'] ?>/calendar">
                                    <?= Helpers::sanitize($v['name']) ?>
                                </a>
                            </h2>
                            <div class="text-xs text-slate-500 mt-1 flex items-center space-x-1">
                                <i class="fa-solid fa-location-dot text-rose-500"></i>
                                <span><?= Helpers::sanitize($v['city']) ?>, <?= Helpers::sanitize($v['district']) ?></span>
                            </div>
                            <div class="flex items-center space-x-4 mt-3 text-xs text-slate-600 border-y border-slate-100 py-2">
                                <span><i class="fa-solid fa-user-group text-slate-400 mr-1"></i><?= $v['max_guests'] ?> Kişi</span>
                                <span><i class="fa-solid fa-bed text-slate-400 mr-1"></i><?= $v['bedrooms'] ?> Oda</span>
                                <span><i class="fa-solid fa-bath text-slate-400 mr-1"></i><?= $v['bathrooms'] ?> Banyo</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2">
                            <a href="<?= APP_URL ?>/villas/<?= $v['id'] ?>/calendar" class="py-2.5 px-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition text-center flex items-center justify-center space-x-1.5">
                                <i class="fa-solid fa-calendar-days text-white"></i>
                                <span class="truncate">Takvim Yönetimi</span>
                            </a>
                            <button onclick="openVillaCalendarPopup(<?= $v['id'] ?>, '<?= Helpers::sanitize($v['name']) ?>')" class="py-2.5 px-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl transition text-center flex items-center justify-center space-x-1.5 border border-indigo-200/80">
                                <i class="fa-solid fa-calendar text-indigo-600"></i>
                                <span class="truncate">Müsaitlik Takvimi</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- FullCalendar Müsaitlik Takvimi Pop-Up Modal -->
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

<script>
const appUrl = '<?= APP_URL ?>';
let currentVillaId = null;
let popupCalendarInstance = null;
const villaReservationsCache = {};

function openVillaCalendarPopup(villaId, villaName) {
    currentVillaId = villaId;
    document.getElementById('popupCalendarVillaName').innerText = villaName;
    document.getElementById('calendarModalPopup').classList.remove('hidden');

    requestAnimationFrame(() => {
        if (!popupCalendarInstance) {
            const calendarEl = document.getElementById('popupCalendar');
            popupCalendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'tr',
                selectable: false,
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
                    month: 'Ay'
                },
                datesSet: function(info) {
                    const cached = villaReservationsCache[currentVillaId];
                    if (cached && cached.length > 0) {
                        requestAnimationFrame(() => {
                            applyPopupCalendarCellStyles(cached);
                        });
                    }
                },
                events: function(info, successCallback, failureCallback) {
                    const cached = villaReservationsCache[currentVillaId];
                    if (cached && cached.length > 0) {
                        const formattedEvents = cached.map(item => ({
                            id: item.id,
                            start: item.start_date || item.start,
                            end: item.end_date || item.end,
                            display: 'background'
                        }));
                        successCallback(formattedEvents);
                        requestAnimationFrame(() => {
                            applyPopupCalendarCellStyles(cached);
                        });
                    }

                    fetch(`${appUrl}/api/villas/${currentVillaId}/all-reservations`)
                        .then(res => res.json())
                        .then(data => {
                            villaReservationsCache[currentVillaId] = Array.isArray(data) ? data : [];
                            const updated = villaReservationsCache[currentVillaId];
                            requestAnimationFrame(() => {
                                applyPopupCalendarCellStyles(updated);
                            });

                            if (!cached || cached.length === 0) {
                                const formattedEvents = updated.map(item => ({
                                    id: item.id,
                                    start: item.start_date || item.start,
                                    end: item.end_date || item.end,
                                    display: 'background'
                                }));
                                successCallback(formattedEvents);
                            }
                        })
                        .catch(err => {
                            if (!cached || cached.length === 0) {
                                failureCallback(err);
                            }
                        });
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

function closeCalendarModalPopup() {
    document.getElementById('calendarModalPopup').classList.add('hidden');
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

        const start = item.start_date || item.start;
        const end = item.end_date || item.end;
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

function filterCalendarVillas() {
    const input = document.getElementById('calSelectSearchInput');
    const clearBtn = document.getElementById('clearCalSelectSearchBtn');
    const query = input ? input.value.trim().toLowerCase() : '';
    const cards = document.querySelectorAll('.cal-villa-card');
    const noResult = document.getElementById('noCalVillaResult');

    if (clearBtn) {
        if (query) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    let matchCount = 0;
    cards.forEach(card => {
        const name = (card.getAttribute('data-name') || '').toLowerCase();
        const location = (card.getAttribute('data-location') || '').toLowerCase();

        if (!query || name.includes(query) || location.includes(query)) {
            card.classList.remove('hidden');
            matchCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    if (noResult) {
        if (matchCount === 0 && query) {
            noResult.classList.remove('hidden');
        } else {
            noResult.classList.add('hidden');
        }
    }
}

function clearCalSelectSearch() {
    const input = document.getElementById('calSelectSearchInput');
    if (input) {
        input.value = '';
        filterCalendarVillas();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.cal-villa-card');
    cards.forEach(card => {
        const villaId = card.getAttribute('data-id');
        if (villaId) {
            fetch(`${appUrl}/api/villas/${villaId}/all-reservations`)
                .then(res => res.json())
                .then(data => {
                    villaReservationsCache[villaId] = Array.isArray(data) ? data : [];
                })
                .catch(() => {});
        }
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
