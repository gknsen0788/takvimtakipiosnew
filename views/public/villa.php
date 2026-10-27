<?php
use App\Helpers;
$ownerContactPhone = !empty($villa['contact_phone']) ? $villa['contact_phone'] : ($ownerPhone ?? '');
$cleanPhone = preg_replace('/[^0-9]/', '', $ownerContactPhone);
if ($cleanPhone && str_starts_with($cleanPhone, '0')) {
    $cleanPhone = '90' . substr($cleanPhone, 1);
} elseif ($cleanPhone && strlen($cleanPhone) === 10) {
    $cleanPhone = '90' . $cleanPhone;
}
?>
<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Helpers::sanitize($villa['name']) ?> - Müsaitlik Takvimi</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FullCalendar v6 -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/locales/tr.global.min.js"></script>

    <!-- Custom App CSS with Cache Buster -->
    <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css?v=<?= time() ?>">
</head>
<body class="h-full flex flex-col justify-between text-slate-800 antialiased">

<div class="max-w-5xl mx-auto w-full p-4 sm:p-6 space-y-5">
    
    <!-- TOP HEADER ELEMENTS (Without card background) -->
    <div class="flex items-center justify-between gap-3 px-1">
        <h1 class="text-sm sm:text-base font-extrabold text-slate-800 tracking-tight flex items-center space-x-2 truncate">
            <i class="fa-solid fa-house-chimney text-indigo-600 text-base shrink-0"></i>
            <span class="truncate"><?= Helpers::sanitize($villa['name']) ?> Müsaitlik Takvimi</span>
        </h1>
        <button onclick="sharePublicPage(this)" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200/90 text-xs font-bold rounded-xl shadow-sm transition active:scale-95 shrink-0">
            <i class="fa-solid fa-share-nodes text-indigo-600 text-xs"></i>
            <span>Paylaş</span>
        </button>
    </div>

    <!-- 2-COLUMN GRID (Equal Top Alignment) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
        
        <!-- LEFT COLUMN: Calendar Card (7 Cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 shadow-sm border border-slate-200/80 space-y-3">
                <!-- Legend -->
                <div class="flex items-center justify-between text-xs border-b border-slate-100 pb-3 w-full">
                    <div class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-white border border-slate-400 inline-block"></span><span class="font-medium text-slate-700">Müsait</span></div>
                    <div class="flex items-center space-x-1.5"><span class="w-3.5 h-3.5 rounded-full border border-slate-300 inline-block shrink-0" style="background: linear-gradient(135deg, #fd5a5f 50%, #ffffff 50%);"></span><span class="font-medium text-slate-700">Giriş / Çıkış</span></div>
                    <div class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-[#fd5a5f] inline-block"></span><span class="font-medium text-slate-700">Dolu / Rezerve</span></div>
                </div>

                <!-- FullCalendar Container -->
                <div id="publicCalendar"></div>
            </div>
        </div>

        <!-- RIGHT SIDEBAR COLUMN: Details & Action Buttons (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 shadow-sm border border-slate-200/80 space-y-4">
                <h2 class="text-sm font-bold text-slate-800 flex items-center space-x-2 border-b border-slate-100 pb-2.5">
                    <i class="fa-solid fa-sliders text-indigo-600"></i>
                    <span>Hızlı İşlemler & Detaylar</span>
                </h2>

                <!-- Resimler & Fiyatlar Buttons Grid -->
                <div class="grid grid-cols-2 gap-2.5">
                    <?php if (!empty($villa['google_drive_link'])): ?>
                        <a href="<?= Helpers::sanitize($villa['google_drive_link']) ?>" target="_blank" class="w-full inline-flex items-center justify-center space-x-1.5 px-3 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/80 font-bold text-xs sm:text-sm rounded-xl shadow-sm transition active:scale-95">
                            <i class="fa-solid fa-images text-indigo-600"></i>
                            <span>Resimler</span>
                        </a>
                    <?php else: ?>
                        <button onclick="openPhotosModal()" class="w-full inline-flex items-center justify-center space-x-1.5 px-3 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/80 font-bold text-xs sm:text-sm rounded-xl shadow-sm transition active:scale-95">
                            <i class="fa-solid fa-images text-indigo-600"></i>
                            <span>Resimler</span>
                        </button>
                    <?php endif; ?>

                    <button onclick="openPricingModal()" class="w-full inline-flex items-center justify-center space-x-1.5 px-3 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/80 font-bold text-xs sm:text-sm rounded-xl shadow-sm transition active:scale-95">
                        <i class="fa-solid fa-tags text-indigo-600"></i>
                        <span>Fiyatlar</span>
                    </button>
                </div>

                <!-- Kişi, Oda & Banyo Info Grid -->
                <div class="grid grid-cols-3 gap-2 pt-0.5">
                    <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 text-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 flex items-center justify-center space-x-1">
                            <i class="fa-solid fa-user-group text-indigo-600"></i>
                            <span>Kapasite</span>
                        </div>
                        <div class="text-xs sm:text-sm font-extrabold text-slate-800">
                            <?= (int)($villa['max_guests'] ?? 4) ?> Kişi
                        </div>
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 text-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 flex items-center justify-center space-x-1">
                            <i class="fa-solid fa-bed text-indigo-600"></i>
                            <span>Oda</span>
                        </div>
                        <div class="text-xs sm:text-sm font-extrabold text-slate-800">
                            <?= (int)($villa['bedrooms'] ?? 2) ?> Oda
                        </div>
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 text-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5 flex items-center justify-center space-x-1">
                            <i class="fa-solid fa-bath text-indigo-600"></i>
                            <span>Banyo</span>
                        </div>
                        <div class="text-xs sm:text-sm font-extrabold text-slate-800">
                            <?= (int)($villa['bathrooms'] ?? 1) ?> Banyo
                        </div>
                    </div>
                </div>

                <!-- Check-in & Check-out Times Box -->
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 flex items-center space-x-1">
                            <i class="fa-solid fa-right-to-bracket text-emerald-600"></i>
                            <span>Giriş Saati</span>
                        </div>
                        <div class="text-xs sm:text-sm font-extrabold text-slate-800">
                            <?= Helpers::sanitize(!empty($villa['checkin_time']) ? $villa['checkin_time'] : '16:00') ?>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 flex items-center space-x-1">
                            <i class="fa-solid fa-right-from-bracket text-rose-500"></i>
                            <span>Çıkış Saati</span>
                        </div>
                        <div class="text-xs sm:text-sm font-extrabold text-slate-800">
                            <?= Helpers::sanitize(!empty($villa['checkout_time']) ? $villa['checkout_time'] : '10:00') ?>
                        </div>
                    </div>
                </div>

                <!-- Depozito & Temizlik Ücreti 2-Column Grid -->
                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Depozito Box -->
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-shield-halved text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate">Depozito</div>
                            <div class="text-xs sm:text-sm font-extrabold text-slate-800 truncate">
                                <?= Helpers::sanitize(!empty($villa['deposit_amount']) ? $villa['deposit_amount'] : 'Yok') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Temizlik Ücreti Box -->
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-broom text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate" title="Temizlik Ücreti <?= !empty($villa['cleaning_days']) ? '(' . (int)$villa['cleaning_days'] . ' Gün Altı)' : '' ?>">
                                Temizlik <?= !empty($villa['cleaning_days']) ? '(' . (int)$villa['cleaning_days'] . 'g Altı)' : '' ?>
                            </div>
                            <div class="text-xs sm:text-sm font-extrabold text-slate-800 truncate">
                                <?= Helpers::sanitize(!empty($villa['cleaning_fee']) ? $villa['cleaning_fee'] : 'Yok') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($villa['pool_heating_fee'])): ?>
                <!-- Havuz Isıtma Ücreti Box (Depozito ve Temizlik Altında) -->
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 flex items-center justify-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-temperature-high text-xs"></i>
                    </div>
                    <div class="text-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Havuz Isıtma Ücreti</div>
                        <div class="text-xs sm:text-sm font-extrabold text-slate-800">
                            <?= Helpers::sanitize($villa['pool_heating_fee']) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Konum Box (Depozito ve Temizlik Altında - Ortalı) -->
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 flex items-center justify-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-location-dot text-xs"></i>
                    </div>
                    <div class="text-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Konum</div>
                        <div class="text-xs sm:text-sm font-extrabold text-slate-800">
                            <?= Helpers::sanitize($villa['city']) ?><?= !empty($villa['district']) ? ' / ' . Helpers::sanitize($villa['district']) : '' ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($villa['google_maps_url'])): ?>
                    <!-- Google Haritalar Link Button -->
                    <a href="<?= Helpers::sanitize($villa['google_maps_url']) ?>" target="_blank" class="w-full inline-flex items-center justify-center space-x-1.5 px-3 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold rounded-2xl transition">
                        <i class="fa-solid fa-map-location-dot text-rose-500"></i>
                        <span>Google Haritalarda Göster</span>
                    </a>
                <?php endif; ?>

                <!-- Call & WhatsApp Action Buttons -->
                <?php if (!empty($cleanPhone)): ?>
                    <div class="grid grid-cols-2 gap-2.5 pt-1">
                        <!-- Call Button -->
                        <a href="tel:+<?= $cleanPhone ?>" 
                           class="w-full inline-flex items-center justify-center space-x-1.5 px-3 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-lg shadow-indigo-600/20 transition active:scale-95">
                            <i class="fa-solid fa-phone text-xs sm:text-sm"></i>
                            <span>Ara</span>
                        </a>

                        <!-- WhatsApp Button -->
                        <a href="https://wa.me/<?= $cleanPhone ?>" 
                           target="_blank" 
                           class="w-full inline-flex items-center justify-center space-x-1.5 px-3 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm rounded-2xl shadow-lg shadow-emerald-600/20 transition active:scale-95">
                            <i class="fa-brands fa-whatsapp text-sm sm:text-base"></i>
                            <span>WhatsApp</span>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>



<script>
const villaId = <?= $villa['id'] ?>;
const appUrl = '<?= APP_URL ?>';
const pricePeriods = <?= json_encode($pricePeriods ?? []) ?>;

function getNightlyPriceForDate(dateStr) {
    if (!pricePeriods || !Array.isArray(pricePeriods)) return null;
    for (let i = 0; i < pricePeriods.length; i++) {
        const pp = pricePeriods[i];
        if (dateStr >= pp.start_date && dateStr <= pp.end_date) {
            return pp.nightly_price ? parseFloat(pp.nightly_price) : null;
        }
    }
    return null;
}

function formatMoneyShort(val) {
    let num = typeof val === 'number' ? val : parseFloat(val);
    if (isNaN(num) || num <= 0) return '';
    return new Intl.NumberFormat('tr-TR').format(num) + ' ₺';
}

function applyPublicCalendarCellStyles(reservations, containerId = '#publicCalendar') {
    document.querySelectorAll(`${containerId} .fc-daygrid-day`).forEach(el => {
        el.style.background = 'transparent';
        const frame = el.querySelector('.fc-daygrid-day-frame') || el;
        const numEl = el.querySelector('.fc-daygrid-day-number');
        const isEmptyCell = el.classList.contains('fc-day-other');

        if (isEmptyCell) {
            frame.style.setProperty('background', 'transparent', 'important');
            frame.style.setProperty('border', 'none', 'important');
            frame.style.setProperty('border-color', 'transparent', 'important');
            frame.style.setProperty('box-shadow', 'none', 'important');
            frame.style.setProperty('opacity', '0', 'important');
            frame.style.setProperty('visibility', 'hidden', 'important');
            frame.style.setProperty('pointer-events', 'none', 'important');
        } else {
            frame.style.setProperty('background', '#ffffff', 'important');
            frame.style.removeProperty('border-color');
            frame.style.removeProperty('box-shadow');
            frame.style.removeProperty('opacity');
            frame.style.removeProperty('pointer-events');
        }
        if (numEl) {
            numEl.style.color = '';
            numEl.style.textShadow = '';
            numEl.style.fontWeight = '';
        }
        const oldPrice = el.querySelector('.cell-price-tag');
        if (oldPrice) oldPrice.remove();
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
        const cell = document.querySelector(`${containerId} .fc-daygrid-day[data-date="${dateStr}"]`);
        if (!cell || cell.classList.contains('fc-day-other')) return;

        const frame = cell.querySelector('.fc-daygrid-day-frame') || cell;
        const info = dayMap[dateStr];
        const numEl = cell.querySelector('.fc-daygrid-day-number');

        if (info.outColor && info.inColor) {
            frame.style.setProperty('background', `linear-gradient(135deg, ${info.outColor} calc(50% - 3px), #ffffff calc(50% - 3px), #ffffff calc(50% + 3px), ${info.inColor} calc(50% + 3px))`, 'important');
            cell.classList.add('fc-has-reservation');
            if (numEl) {
                numEl.style.setProperty('color', '#ffffff', 'important');
                numEl.style.setProperty('text-shadow', '0 1px 2px rgba(0, 0, 0, 0.5)', 'important');
                numEl.style.setProperty('font-weight', '700', 'important');
            }
        } else if (info.isFull) {
            frame.style.setProperty('background', info.fullColor, 'important');
            cell.classList.add('fc-has-reservation');
            if (numEl) {
                numEl.style.setProperty('color', '#ffffff', 'important');
                numEl.style.setProperty('text-shadow', '0 1px 2px rgba(0, 0, 0, 0.5)', 'important');
                numEl.style.setProperty('font-weight', '700', 'important');
            }
        } else if (info.outColor) {
            frame.style.setProperty('background', `linear-gradient(135deg, ${info.outColor} 49%, #ffffff 51%)`, 'important');
            if (numEl) {
                numEl.style.setProperty('color', '#1e293b', 'important');
                numEl.style.removeProperty('text-shadow');
                numEl.style.setProperty('font-weight', '700', 'important');
            }
        } else if (info.inColor) {
            frame.style.setProperty('background', `linear-gradient(135deg, #ffffff 49%, ${info.inColor} 51%)`, 'important');
            if (numEl) {
                numEl.style.setProperty('color', '#1e293b', 'important');
                numEl.style.removeProperty('text-shadow');
                numEl.style.setProperty('font-weight', '700', 'important');
            }
        }
    });

    // Inject seasonal price badges inside day cells
    document.querySelectorAll(`${containerId} .fc-daygrid-day`).forEach(cell => {
        if (cell.classList.contains('fc-day-other')) return;
        const dateStr = cell.getAttribute('data-date');
        if (!dateStr) return;

        const price = getNightlyPriceForDate(dateStr);
        if (price) {
            const formattedPrice = formatMoneyShort(price);
            const frame = cell.querySelector('.fc-daygrid-day-frame') || cell.querySelector('.fc-daygrid-day-top');
            if (frame && !cell.querySelector('.cell-price-tag')) {
                const priceDiv = document.createElement('div');
                priceDiv.className = 'cell-price-tag';
                priceDiv.innerText = formattedPrice;
                frame.appendChild(priceDiv);
            }
        }
    });
}

let publicReservationsCache = [];
let popupYearlyCalendarInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('publicCalendar');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'tr',
        selectable: false,
        fixedWeekCount: false,
        showNonCurrentDates: false,
        stickyHeaderDates: false,
        height: 'auto',
        aspectRatio: 1.15,
        expandRows: false,
        headerToolbar: {
            left: 'prev',
            center: 'title',
            right: 'fullScreenBtn next'
        },
        customButtons: {
            fullScreenBtn: {
                text: '',
                hint: 'Tam Ekran / 1 Yıllık Takvim',
                click: function() {
                    openYearlyCalendarModal();
                }
            }
        },
        datesSet: function(info) {
            if (publicReservationsCache.length > 0) {
                requestAnimationFrame(() => {
                    applyPublicCalendarCellStyles(publicReservationsCache, '#publicCalendar');
                });
            }
        },
        events: function(info, successCallback, failureCallback) {
            // Serve instantly from cache if available
            if (publicReservationsCache.length > 0) {
                const formatted = publicReservationsCache.map(item => ({
                    id: item.id,
                    start: item.start_date || item.start,
                    end: item.end_date || item.end,
                    display: 'background'
                }));
                successCallback(formatted);
                requestAnimationFrame(() => {
                    applyPublicCalendarCellStyles(publicReservationsCache, '#publicCalendar');
                });
            }

            // Fetch wide date range (3 years around current view) to populate cache
            const startYear = parseInt(info.startStr.substring(0, 4)) - 1;
            const endYear = parseInt(info.endStr.substring(0, 4)) + 1;
            const prefetchStart = `${startYear}-01-01`;
            const prefetchEnd = `${endYear}-12-31`;

            fetch(`${appUrl}/api/public/villas/${villaId}/calendar?start=${prefetchStart}&end=${prefetchEnd}`)
                .then(res => res.json())
                .then(data => {
                    publicReservationsCache = Array.isArray(data) ? data : [];
                    requestAnimationFrame(() => {
                        applyPublicCalendarCellStyles(publicReservationsCache, '#publicCalendar');
                        if (popupYearlyCalendarInstance) {
                            applyPublicCalendarCellStyles(publicReservationsCache, '#popupYearlyCalendar');
                        }
                    });

                    if (publicReservationsCache.length > 0) {
                        const formatted = publicReservationsCache.map(item => ({
                            id: item.id,
                            start: item.start_date || item.start,
                            end: item.end_date || item.end,
                            display: 'background'
                        }));
                        successCallback(formatted);
                    }
                })
                .catch(err => {
                    if (publicReservationsCache.length === 0) {
                        failureCallback(err);
                    }
                });
        }
    });

    calendar.render();

    // Style FullScreen Icon Button on Main Calendar Header
    const fsBtn = document.querySelector('#publicCalendar .fc-fullScreenBtn-button');
    if (fsBtn) {
        fsBtn.innerHTML = '<i class="fa-solid fa-expand text-indigo-600 text-xs sm:text-sm"></i>';
        fsBtn.title = 'Tam Ekran / 1 Yıllık Takvim';
    }
    
    window.publicMainCalendar = calendar;
});

const TURKISH_MONTHS = [
    'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
    'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'
];

let currentYearlyViewYear = new Date().getFullYear();

function render12MonthsYearlyCalendar(year) {
    currentYearlyViewYear = year;
    const container = document.getElementById('yearly12MonthsGrid');
    const titleEl = document.getElementById('yearlyModalYearTitle');
    const modalSelect = document.getElementById('yearlyModalYearSelect');
    if (!container) return;

    if (titleEl) {
        titleEl.innerText = `${year} Yılı Müsaitlik Takvimi`;
    }

    if (modalSelect) {
        const curY = new Date().getFullYear();
        if (modalSelect.options.length === 0) {
            let optionsHtml = '';
            for (let y = curY - 3; y <= curY + 5; y++) {
                optionsHtml += `<option value="${y}">${y}</option>`;
            }
            modalSelect.innerHTML = optionsHtml;
        }
        modalSelect.value = year;
    }

    // Build reservation day map from publicReservationsCache
    const dayMap = {};
    if (Array.isArray(publicReservationsCache)) {
        publicReservationsCache.forEach(item => {
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
    }

    let html = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-2.5 lg:gap-2.5 items-start">';

    for (let m = 0; m < 12; m++) {
        const monthName = TURKISH_MONTHS[m];
        const daysInMonth = new Date(year, m + 1, 0).getDate();
        
        // Monday start: 0 = Mon, 1 = Tue, ..., 6 = Sun
        const firstDayObj = new Date(year, m, 1);
        const startDayIndex = (firstDayObj.getDay() + 6) % 7;

        html += `
            <div class="bg-white p-2 sm:p-2.5 lg:p-2 rounded-2xl border border-slate-200/90 shadow-2xs space-y-1 lg:space-y-1 flex flex-col justify-between">
                <div class="text-center font-extrabold text-slate-800 text-xs sm:text-sm lg:text-xs border-b border-slate-100 pb-1 lg:pb-0.5">
                    ${monthName} ${year}
                </div>
                
                <div class="grid grid-cols-7 gap-0.5 text-center text-[9px] sm:text-[10px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-tighter mb-0.5 lg:mb-0">
                    <div>Pzt</div>
                    <div>Sal</div>
                    <div>Çar</div>
                    <div>Per</div>
                    <div>Cum</div>
                    <div>Cmt</div>
                    <div>Paz</div>
                </div>

                <div class="grid grid-cols-7 gap-0.5 text-center font-bold">
        `;

        // Empty cells before start of month
        for (let empty = 0; empty < startDayIndex; empty++) {
            html += `<div class="w-full aspect-square"></div>`;
        }

        // Days of month
        for (let d = 1; d <= daysInMonth; d++) {
            const monthStr = String(m + 1).padStart(2, '0');
            const dayStr = String(d).padStart(2, '0');
            const dateKey = `${year}-${monthStr}-${dayStr}`;

            const info = dayMap[dateKey] || {};
            let cellStyle = 'background-color: #ffffff; color: #334155; border: 1px solid #e2e8f0;';

            if (info.outColor && info.inColor) {
                cellStyle = `background: linear-gradient(135deg, ${info.outColor} calc(50% - 2px), #ffffff calc(50% - 2px), #ffffff calc(50% + 2px), ${info.inColor} calc(50% + 2px)) !important; color: #ffffff; border: none; text-shadow: 0 1px 2px rgba(0,0,0,0.5);`;
            } else if (info.isFull) {
                cellStyle = `background-color: ${info.fullColor} !important; color: #ffffff !important; border: none; text-shadow: 0 1px 2px rgba(0,0,0,0.5);`;
            } else if (info.outColor) {
                cellStyle = `background: linear-gradient(135deg, ${info.outColor} 49%, #ffffff 51%) !important; color: #1e293b; border: 1px solid #cbd5e1;`;
            } else if (info.inColor) {
                cellStyle = `background: linear-gradient(135deg, #ffffff 49%, ${info.inColor} 51%) !important; color: #1e293b; border: 1px solid #cbd5e1;`;
            }

            html += `
                <div class="w-full aspect-square rounded-md sm:rounded-lg flex items-center justify-center font-bold text-[10px] sm:text-xs leading-none transition" style="${cellStyle}">
                    ${d}
                </div>
            `;
        }

        html += `
                </div>
            </div>
        `;
    }

    html += '</div>';
    container.innerHTML = html;
}

function prevYearlyCalendar() {
    render12MonthsYearlyCalendar(currentYearlyViewYear - 1);
}

function nextYearlyCalendar() {
    render12MonthsYearlyCalendar(currentYearlyViewYear + 1);
}

function openYearlyCalendarModal() {
    const modal = document.getElementById('yearlyCalendarModalPopup');
    if (!modal) return;
    modal.classList.remove('hidden');
    const activeYear = window.publicMainCalendar ? window.publicMainCalendar.getDate().getFullYear() : new Date().getFullYear();
    render12MonthsYearlyCalendar(activeYear);
}

function closeYearlyCalendarModal() {
    const modal = document.getElementById('yearlyCalendarModalPopup');
    if (modal) modal.classList.add('hidden');
}

function openPricingModal() {
    document.getElementById('pricingModalPopup').classList.remove('hidden');
}
function closePricingModal() {
    document.getElementById('pricingModalPopup').classList.add('hidden');
}

function openPhotosModal() {
    document.getElementById('photosModalPopup').classList.remove('hidden');
}
function closePhotosModal() {
    document.getElementById('photosModalPopup').classList.add('hidden');
}

function sharePublicPage(btn) {
    const url = window.location.href;
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: url
        }).catch(() => {
            copyPublicUrl(url, btn);
        });
    } else {
        copyPublicUrl(url, btn);
    }
}

function copyPublicUrl(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check text-emerald-600 text-xs"></i> <span class="text-emerald-700">Kopyalandı!</span>';
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 2000);
    });
}
</script>

<!-- Photos Modal Popup -->
<div id="photosModalPopup" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-4 sm:p-6 space-y-4 border border-slate-100 relative">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 sm:pb-4">
            <div class="flex items-center space-x-2.5 sm:space-x-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-images text-base sm:text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base"><?= Helpers::sanitize($villa['name']) ?> - Resimler</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500">Görseller ve fotoğraf galerisi</p>
                </div>
            </div>
            <button onclick="closePhotosModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center text-sm font-bold transition shrink-0">&times;</button>
        </div>

        <?php if (!empty($villa['google_drive_link'])): ?>
            <div class="p-4 bg-indigo-50/70 border border-indigo-100 rounded-2xl flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-brands fa-google-drive text-2xl text-indigo-600"></i>
                    <div>
                        <div class="text-xs font-bold text-slate-800">Google Drive Görsel Klasörü</div>
                        <div class="text-[11px] text-slate-500">Tüm yüksek çözünürlüklü fotoğrafları görüntüleyin</div>
                    </div>
                </div>
                <a href="<?= Helpers::sanitize($villa['google_drive_link']) ?>" target="_blank" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    İncele <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                </a>
            </div>
        <?php endif; ?>

        <?php if (empty($villaImages) && empty($villa['google_drive_link'])): ?>
            <div class="text-center py-8 text-slate-400 text-xs">
                <i class="fa-solid fa-image text-3xl text-slate-300 mb-2"></i>
                <p class="font-medium text-slate-500">Henüz yüklü görsel bulunmuyor.</p>
            </div>
        <?php elseif (!empty($villaImages)): ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-80 overflow-y-auto p-1">
                <?php foreach ($villaImages as $img): ?>
                    <a href="<?= APP_URL . Helpers::sanitize($img['image_url']) ?>" target="_blank" class="block aspect-square rounded-2xl overflow-hidden border border-slate-200 hover:opacity-90 transition shadow-sm">
                        <img src="<?= APP_URL . Helpers::sanitize($img['image_url']) ?>" class="w-full h-full object-cover">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="pt-1 flex items-center justify-end">
            <button onclick="closePhotosModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                Kapat
            </button>
        </div>
    </div>
</div>

<!-- Pricing Modal Popup -->
<div id="pricingModalPopup" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-4 sm:p-6 space-y-4 border border-slate-100 relative">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 sm:pb-4">
            <div class="flex items-center space-x-2.5 sm:space-x-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-tags text-base sm:text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base"><?= Helpers::sanitize($villa['name']) ?> - Dönemsel Fiyatlar</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500">Konaklama dönemlerine göre gecelik ve haftalık fiyatlar</p>
                </div>
            </div>
            <button onclick="closePricingModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center text-sm font-bold transition shrink-0">&times;</button>
        </div>

        <?php if (empty($pricePeriods)): ?>
            <div class="text-center py-8 text-slate-400 text-xs">
                <i class="fa-solid fa-tag text-3xl text-slate-300 mb-2"></i>
                <p class="font-medium text-slate-500">Henüz tanımlanmış dönemsel fiyat bulunmuyor.</p>
                <p class="text-slate-400 text-[11px] mt-1">Detaylı fiyat bilgisi ve müsaitlik için WhatsApp üzerinden iletişime geçebilirsiniz.</p>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border border-slate-100 overflow-hidden w-full">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-2 sm:px-4 whitespace-nowrap">Tarih Aralığı</th>
                            <th class="py-2.5 px-1.5 sm:px-4 whitespace-nowrap text-right sm:text-left">Gecelik</th>
                            <th class="py-2.5 px-1.5 sm:px-4 whitespace-nowrap text-right sm:text-left">Haftalık</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($pricePeriods as $pp): ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-2.5 px-2 sm:px-4 font-semibold text-slate-800 text-[11px] sm:text-xs whitespace-nowrap"><?= Helpers::formatDateTR($pp['start_date']) ?> ➔ <?= Helpers::formatDateTR($pp['end_date']) ?></td>
                                <td class="py-2.5 px-1.5 sm:px-4 font-bold text-emerald-600 text-[11px] sm:text-sm whitespace-nowrap text-right sm:text-left"><?= number_format((float)$pp['nightly_price'], 0, ',', '.') ?> TL</td>
                                <td class="py-2.5 px-1.5 sm:px-4 font-bold text-indigo-600 text-[11px] sm:text-sm whitespace-nowrap text-right sm:text-left"><?= number_format((float)($pp['weekly_price'] ?? ($pp['nightly_price'] * 7)), 0, ',', '.') ?> TL</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="pt-1 flex items-center justify-between">
            <span class="text-[10px] sm:text-[11px] text-slate-400 italic">Fiyatlar belirtilen dönemler için geçerlidir.</span>
            <button onclick="closePricingModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                Kapat
            </button>
        </div>
    </div>
</div>

<!-- 1-Year (12-Month) Responsive Popup Modal (6x2 Desktop No Scroll, 2x6 Mobile Scroll) -->
<div id="yearlyCalendarModalPopup" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-0 sm:p-2 lg:p-2.5 hidden animate-fadeIn">
    <div class="bg-white w-full h-full lg:h-auto lg:max-h-[98vh] max-w-none lg:max-w-[1600px] lg:w-[98vw] rounded-none sm:rounded-3xl shadow-2xl p-3 sm:p-3.5 lg:p-3 space-y-2 sm:space-y-2.5 lg:space-y-2 border-0 sm:border sm:border-slate-100 relative flex flex-col overflow-hidden">
        
        <!-- Header -->
        <div class="border-b border-slate-100 pb-2 lg:pb-1.5 shrink-0 space-y-1.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 text-indigo-600 font-extrabold text-[11px] sm:text-xs tracking-wider uppercase">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 inline-block"></span>
                    <span><?= Helpers::sanitize($villa['name']) ?></span>
                </div>
                <!-- Close Button -->
                <button onclick="closeYearlyCalendarModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-slate-100 text-slate-400 hover:bg-slate-200 flex items-center justify-center text-sm font-bold transition shrink-0">&times;</button>
            </div>

            <div class="flex flex-col sm:flex-row items-center sm:justify-start gap-2 sm:gap-3">
                <h3 class="font-extrabold text-slate-800 text-base sm:text-lg lg:text-xl text-center sm:text-left" id="yearlyModalYearTitle">2026 Yılı Müsaitlik Takvimi</h3>
                
                <!-- Year Selector Control (Centered on Mobile, Beside Title on Desktop) -->
                <div class="flex items-center justify-center space-x-1 bg-slate-100 p-1 rounded-xl mx-auto sm:mx-0">
                    <button onclick="prevYearlyCalendar()" class="w-7.5 h-7.5 sm:w-7 sm:h-7 rounded-lg bg-white hover:bg-slate-50 text-slate-700 flex items-center justify-center text-xs font-bold shadow-xs transition" title="Önceki Yıl">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <select id="yearlyModalYearSelect" onchange="render12MonthsYearlyCalendar(parseInt(this.value))" class="bg-white text-slate-800 text-xs font-bold py-1 px-3 rounded-lg border border-slate-200 cursor-pointer outline-none focus:ring-2 focus:ring-indigo-500">
                    </select>
                    <button onclick="nextYearlyCalendar()" class="w-7.5 h-7.5 sm:w-7 sm:h-7 rounded-lg bg-white hover:bg-slate-50 text-slate-700 flex items-center justify-center text-xs font-bold shadow-xs transition" title="Sonraki Yıl">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Legend Bar -->
        <div class="flex items-center justify-between sm:justify-center space-x-3 sm:space-x-8 lg:space-x-12 text-xs px-3.5 py-1.5 sm:py-2 lg:py-1 bg-slate-50/90 rounded-2xl border border-slate-100 font-medium text-slate-700 shrink-0">
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-full bg-white border border-slate-300 inline-block"></span>
                <span>Müsait</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-full border border-slate-300 inline-block shrink-0" style="background: linear-gradient(135deg, #fd5a5f 50%, #ffffff 50%);"></span>
                <span>Giriş / Çıkış</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded-full bg-[#fd5a5f] inline-block"></span>
                <span>Rezerve</span>
            </div>
        </div>

        <!-- 12-Month Calendar Container (Mobile: Scroll 2x6, Desktop: No Scroll 6x2) -->
        <div id="yearly12MonthsGrid" class="flex-1 overflow-y-auto lg:overflow-hidden p-1 pr-1.5"></div>
    </div>
</div>

</body>
</html>
