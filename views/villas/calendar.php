<?php
use App\Helpers;
$title = Helpers::sanitize($villa['name']) . ' - Takvim Yönetimi';
require __DIR__ . '/../layouts/header.php';
?>


<div class="max-w-6xl mx-auto mb-6 space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="<?= APP_URL ?>/villas" class="hover:underline">Villalarım</a>
                <span>/</span>
                <span class="font-semibold text-slate-700"><?= Helpers::sanitize($villa['name']) ?></span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Takvim Yönetimi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Takvim üzerinde giriş çıkış tarihlerini seçerek bloke işlemi yapabilirsiniz</p>
        </div>
    </div>

    <!-- 2-Column Layout for Desktop (Left: Square Calendar, Right: Closed Dates List) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: Calendar Grid -->
        <div class="lg:col-span-6 space-y-4">
            <!-- Calendar Legend -->
            <div class="bg-white px-4 py-3 rounded-2xl shadow-sm border border-slate-200/90 flex items-center justify-between text-xs w-full">
                <div class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-white border border-slate-400 inline-block"></span><span class="font-medium text-slate-700">Müsait</span></div>
                <div class="flex items-center space-x-1.5"><span class="w-3.5 h-3.5 rounded-full border border-slate-300 inline-block shrink-0" style="background: linear-gradient(135deg, #fd5a5f 50%, #ffffff 50%);"></span><span class="font-medium text-slate-700">Giriş / Çıkış</span></div>
                <div class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-[#fd5a5f] inline-block"></span><span class="font-medium text-slate-700">Rezerve / Kapalı</span></div>
            </div>

            <!-- Compact Square FullCalendar Container -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200/90">
                <div id="calendar"></div>
            </div>
        </div>

                <!-- Right Column: Kapatılan & Rezerve Tarihler Listesi Card -->
        <div class="lg:col-span-6 bg-white rounded-2xl shadow-sm border border-slate-200/90 p-4 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-base sm:text-lg font-bold text-slate-800 flex items-center space-x-2">
                    <i class="fa-solid fa-lock text-slate-700"></i>
                    <span>Kapatılan & Rezerve Tarihler Listesi</span>
                </h2>
            </div>

            <!-- Search Bar -->
            <div class="w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" id="sidebarSearchInput" oninput="filterClosedDatesSidebar()" placeholder="Giriş tarihi ara (Örn: 27.08, 02.09, Eylül...)" class="w-full pl-8 pr-8 py-2.5 rounded-xl border border-slate-200 text-xs font-medium outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/60 placeholder-slate-400">
                <button id="clearSearchBtn" onclick="clearSidebarSearch()" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs font-bold">&times;</button>
            </div>

            <!-- Scrollable Reservations List -->
            <div id="closedDatesList" class="space-y-2.5 max-h-[460px] overflow-y-auto pr-1">
                <div class="text-center py-8 text-slate-400 text-xs">
                    <i class="fa-regular fa-calendar-xmark text-2xl text-slate-300 mb-1"></i>
                    <p>Kayıtlı kapalı veya rezerve tarih bulunmuyor.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Modal / Bottom Drawer for Mobile -->
<div id="actionModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 hidden overflow-y-auto p-3 sm:p-6">
    <div class="min-h-full flex items-center justify-center">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-100 relative my-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 bg-indigo-50 text-indigo-700 rounded-md text-[11px] font-bold mb-1">
                        <i class="fa-solid fa-house text-[10px]"></i>
                        <span><?= Helpers::sanitize($villa['name']) ?></span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Yeni Tarih Aralığı İşlemi</h3>
                    <div class="text-xs font-semibold text-indigo-600 mt-0.5" id="selectedDateText">-- / --</div>
                </div>
                <button type="button" onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center text-sm font-bold shrink-0">&times;</button>
            </div>

            <!-- Dynamic Error Banner in Modal -->
            <div id="modalError" class="hidden mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl"></div>

            <form id="reservationForm" onsubmit="handleReservationSubmit(event)" class="space-y-4">
                <input type="hidden" name="villa_id" value="<?= $villa['id'] ?>">
                <input type="hidden" name="start_date" id="inputStartDate">
                <input type="hidden" name="end_date" id="inputEndDate">
                <input type="hidden" name="reservation_id" id="inputReservationId">

                <!-- Action Selector Buttons -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">İşlem Türü</label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <!-- Button 1: Acente Rezervasyonu (sol üst) -->
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="agency" checked onclick="toggleFields('agency')" class="peer sr-only">
                            <div class="py-2.5 px-1.5 text-center font-bold text-[11px] sm:text-xs rounded-xl border-2 border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 transition whitespace-nowrap flex items-center justify-center space-x-1">
                                <i class="fa-solid fa-briefcase text-indigo-600 text-[11px] sm:text-xs shrink-0"></i>
                                <span>Acente Rezervasyonu</span>
                            </div>
                        </label>
                        <!-- Button 2: Ev Sahibi Rezervasyonu (sağ üst) -->
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="booked" onclick="toggleFields('booked')" class="peer sr-only">
                            <div class="py-2.5 px-1.5 text-center font-bold text-[11px] sm:text-xs rounded-xl border-2 border-slate-200 peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 transition whitespace-nowrap flex items-center justify-center space-x-1">
                                <i class="fa-solid fa-user-shield text-rose-500 text-[11px] sm:text-xs shrink-0"></i>
                                <span>Ev Sahibi Rezervasyonu</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Booking Specific Fields (Hidden by Default) -->
                <div id="bookingFields" class="hidden space-y-3 pt-2">
                    <!-- Hidden main input for booking_date submitted in form -->
                    <input type="hidden" name="booking_date" id="inputBookingDate">

                    <!-- Acente Rezervasyonu Kutusu (Sadece Acente Rezervasyonu Seçildiğinde Görünür: Yan Yana Acente Adı & Rezervasyon Alınma Tarihi) -->
                    <div id="agencyNameBox" class="hidden">
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate" title="Acente Adı (İsteğe Bağlı)">Acente Adı</label>
                                <input type="text" name="agency_name" id="inputAgencyName" placeholder="Örn: TUI, Mavitur" class="w-full px-2.5 sm:px-3 py-2 rounded-xl border border-indigo-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500 bg-indigo-50/40 font-medium">
                            </div>
                            <div>
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate" title="Rezervasyon Alınma Tarihi">Rez. Alınma Tarihi</label>
                                <input type="date" id="inputBookingDateAgency" disabled onchange="syncBookingDate('agency')" class="w-full px-2 sm:px-3 py-2 rounded-xl border border-indigo-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-indigo-50/40">
                            </div>
                        </div>
                    </div>

                    <!-- Müşteri & Ödeme Detay Kutuları (Sadece Ev Sahibi Rezervasyonu Seçildiğinde Görünür) -->
                    <div id="customerAndPriceFields" class="space-y-3">
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div class="min-w-0">
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Müşteri Ad Soyad</label>
                                <input type="text" name="customer_name" id="inputCustomerName" placeholder="Ahmet Bey" class="w-full min-w-0 max-w-full px-2.5 sm:px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Telefon Numarası</label>
                                <input type="tel" name="customer_phone" id="inputCustomerPhone" placeholder="0532 000 00 00" class="w-full min-w-0 max-w-full px-2.5 sm:px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <div class="min-w-0">
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate" title="Rezervasyon Alınma Tarihi">Rez. Alınma Tarihi</label>
                                <input type="date" id="inputBookingDateOwner" disabled onchange="syncBookingDate('owner')" class="w-full min-w-0 max-w-full px-2 sm:px-3 py-2 rounded-xl border border-slate-300 text-xs font-medium text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50">
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Rezervasyon Kaynağı</label>
                                <select name="source" id="inputSource" class="w-full min-w-0 max-w-full px-2 sm:px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                                    <!-- Options dynamically loaded by toggleFields() -->
                                </select>
                            </div>
                        </div>

                        <!-- Financial Fields: Toplam Fiyat, Ön Ödeme, Kalan Ödeme (Formatted as Turkish Currency) -->
                        <div class="grid grid-cols-3 gap-1.5 sm:gap-3">
                            <div class="min-w-0">
                                <label class="block text-[10px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Toplam Fiyat</label>
                                <div class="relative flex items-center">
                                    <input type="text" name="price" id="inputPrice" oninput="handleMoneyInput(this)" placeholder="0" class="w-full min-w-0 max-w-full pl-2 sm:pl-3 pr-6 py-2 rounded-xl border border-slate-300 text-[11px] sm:text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                                    <span class="absolute right-2.5 text-slate-400 font-bold text-xs pointer-events-none">₺</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[10px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Ön Ödeme</label>
                                <div class="relative flex items-center">
                                    <input type="text" name="down_payment" id="inputDownPayment" oninput="handleMoneyInput(this)" placeholder="0" class="w-full min-w-0 max-w-full pl-2 sm:pl-3 pr-6 py-2 rounded-xl border border-slate-300 text-[11px] sm:text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500">
                                    <span class="absolute right-2.5 text-slate-400 font-bold text-xs pointer-events-none">₺</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-[10px] sm:text-xs font-semibold text-slate-700 mb-1 truncate">Kalan Ödeme</label>
                                <div class="relative flex items-center">
                                    <input type="text" name="remaining_payment" id="inputRemainingPayment" oninput="handleMoneyInput(this)" placeholder="0" class="w-full min-w-0 max-w-full pl-2 sm:pl-3 pr-6 py-2 rounded-xl border border-slate-300 text-[11px] sm:text-xs font-bold text-emerald-700 bg-emerald-50/50 outline-none focus:ring-2 focus:ring-indigo-500">
                                    <span class="absolute right-2.5 text-emerald-600 font-bold text-xs pointer-events-none">₺</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Not / Özel İstekler</label>
                            <textarea name="note" id="inputNote" rows="2" placeholder="Örn: 2 çocuklu aile, bebek yatağı eklenecek." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit & Action buttons -->
                <div id="modalButtons" class="pt-3 flex items-center space-x-2">
                    <button type="submit" id="btnSubmitForm" class="flex-grow py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-lg transition active:scale-[0.99] flex items-center justify-center space-x-1.5">
                        <span id="btnSubmitText">Rezervasyon Kaydet</span>
                    </button>

                    <a id="whatsappBtn" href="#" target="_blank" class="hidden py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5 active:scale-[0.99] shrink-0">
                        <i class="fa-brands fa-whatsapp text-sm sm:text-base"></i>
                        <span>WhatsApp'tan Yaz</span>
                    </a>

                    <button type="button" id="btnCancelModal" onclick="closeModal()" class="py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition shrink-0">
                        İptal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let calendar;
let pendingStartDate = null;
let masterReservationsList = [];
let lastCalendarData = null;
const villaId = <?= $villa['id'] ?>;
const appUrl = '<?= APP_URL ?>';
const currentVillaData = {
    name: <?= json_encode($villa['name']) ?>,
    checkin_time: <?= json_encode($villa['checkin_time'] ?? '') ?>,
    checkout_time: <?= json_encode($villa['checkout_time'] ?? '') ?>,
    deposit_amount: <?= json_encode($villa['deposit_amount'] ?? '') ?>,
    pool_heating_fee: <?= json_encode($villa['pool_heating_fee'] ?? '') ?>
};

function cancelSelection() {
    pendingStartDate = null;
    document.querySelectorAll('.fc-pending-start-cell, .fc-selected-range-cell').forEach(el => {
        el.classList.remove('fc-pending-start-cell');
        el.classList.remove('fc-selected-range-cell');
        const frame = el.querySelector('.fc-daygrid-day-frame') || el;
        frame.style.removeProperty('background');
        frame.style.removeProperty('border-color');
        frame.style.removeProperty('box-shadow');
        frame.style.boxShadow = '';
        const numEl = el.querySelector('.fc-daygrid-day-number');
        if (numEl) {
            numEl.style.removeProperty('color');
            numEl.style.removeProperty('text-shadow');
            numEl.style.removeProperty('font-weight');
        }
    });
    if (lastCalendarData) {
        applyCalendarCellStyles(lastCalendarData);
    }
    const banner = document.getElementById('selectionBanner');
    if (banner) banner.classList.add('hidden');
    if (calendar) calendar.unselect();
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
    'generic_ical': 'Dış Acente',
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

function setBookingDateValue(val) {
    const today = val || getTodayString();
    document.getElementById('inputBookingDate').value = today;
    const agencyInput = document.getElementById('inputBookingDateAgency');
    const ownerInput = document.getElementById('inputBookingDateOwner');
    if (agencyInput) agencyInput.value = today;
    if (ownerInput) ownerInput.value = today;
}

function syncBookingDate(type) {
    if (type === 'agency') {
        const val = document.getElementById('inputBookingDateAgency').value;
        setBookingDateValue(val);
    } else {
        const val = document.getElementById('inputBookingDateOwner').value;
        setBookingDateValue(val);
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

    calcRemainingPayment();
}

function calcRemainingPayment() {
    const price = parseMoneyTR(document.getElementById('inputPrice').value);
    const down = parseMoneyTR(document.getElementById('inputDownPayment').value);
    if (price > 0 || down > 0) {
        const remaining = Math.max(0, price - down);
        document.getElementById('inputRemainingPayment').value = remaining > 0 ? new Intl.NumberFormat('tr-TR').format(remaining) : '';
    } else {
        document.getElementById('inputRemainingPayment').value = '';
    }
}

function applyCalendarCellStyles(reservations) {
    // Clear previous cell backgrounds and date number styles
    document.querySelectorAll('.fc-daygrid-day').forEach(el => {
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
            frame.style.boxShadow = '';
        }
        el.classList.remove('fc-has-reservation');
        if (numEl) {
            numEl.style.removeProperty('color');
            numEl.style.removeProperty('text-shadow');
            numEl.style.removeProperty('font-weight');
        }
    });

    const dayMap = {};

    reservations.forEach(item => {
        let color = '#fd5a5f'; // Kapalı / Rezerve (#fd5a5f)
        if (item.status === 'blocked') color = '#fd5a5f';
        else if (item.status === 'optional') color = '#fef3c7';

        const start = item.start_date; // Check-in date 'YYYY-MM-DD'
        const end = item.end_date;     // Check-out date 'YYYY-MM-DD'

        // Check-in day
        if (!dayMap[start]) dayMap[start] = {};
        dayMap[start].inColor = color;

        // Check-out day
        if (!dayMap[end]) dayMap[end] = {};
        dayMap[end].outColor = color;

        // Full stay days between start and end (start < D < end)
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

    // Apply backgrounds and white date number colors to DOM elements
    Object.keys(dayMap).forEach(dateStr => {
        const cell = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"]`);
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

    highlightPendingStartDate(pendingStartDate);
}

function highlightPendingStartDate(dateStr) {
    document.querySelectorAll('.fc-pending-start-cell, .fc-selected-range-cell').forEach(el => {
        el.classList.remove('fc-pending-start-cell');
        el.classList.remove('fc-selected-range-cell');
        if (!el.classList.contains('fc-has-reservation')) {
            const frame = el.querySelector('.fc-daygrid-day-frame') || el;
            frame.style.removeProperty('background');
            frame.style.removeProperty('border-color');
            frame.style.removeProperty('box-shadow');
            const numEl = el.querySelector('.fc-daygrid-day-number');
            if (numEl) {
                numEl.style.removeProperty('color');
                numEl.style.removeProperty('text-shadow');
            }
        }
    });

    if (dateStr) {
        const cell = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"]`);
        if (cell) {
            cell.classList.add('fc-pending-start-cell');
            const frame = cell.querySelector('.fc-daygrid-day-frame') || cell;
            frame.style.setProperty('background', '#fd5a5f', 'important');
            frame.style.setProperty('border-color', 'transparent', 'important');
            frame.style.setProperty('box-shadow', '0 2px 8px rgba(253, 90, 95, 0.3)', 'important');
            const numEl = cell.querySelector('.fc-daygrid-day-number');
            if (numEl) {
                numEl.style.setProperty('color', '#ffffff', 'important');
                numEl.style.setProperty('text-shadow', '0 1px 2px rgba(0, 0, 0, 0.3)', 'important');
                numEl.style.setProperty('font-weight', '800', 'important');
            }
        }
    }
}

function highlightRangeSelection(startStr, endStr) {
    document.querySelectorAll('.fc-pending-start-cell, .fc-selected-range-cell').forEach(el => {
        el.classList.remove('fc-pending-start-cell');
        el.classList.remove('fc-selected-range-cell');
    });

    let curr = new Date(startStr + 'T00:00:00');
    const endDateObj = new Date(endStr + 'T00:00:00');

    while (curr <= endDateObj) {
        const y = curr.getFullYear();
        const m = String(curr.getMonth() + 1).padStart(2, '0');
        const d = String(curr.getDate()).padStart(2, '0');
        const dStr = `${y}-${m}-${d}`;

        const cell = document.querySelector(`.fc-daygrid-day[data-date="${dStr}"]`);
        if (cell) {
            cell.classList.add('fc-selected-range-cell');
            const frame = cell.querySelector('.fc-daygrid-day-frame') || cell;
            frame.style.setProperty('background', '#fd5a5f', 'important');
            frame.style.setProperty('border-color', 'transparent', 'important');
            frame.style.setProperty('box-shadow', '0 2px 8px rgba(253, 90, 95, 0.3)', 'important');
            const numEl = cell.querySelector('.fc-daygrid-day-number');
            if (numEl) {
                numEl.style.setProperty('color', '#ffffff', 'important');
                numEl.style.setProperty('text-shadow', '0 1px 2px rgba(0, 0, 0, 0.3)', 'important');
                numEl.style.setProperty('font-weight', '800', 'important');
            }
        }
        curr.setDate(curr.getDate() + 1);
    }
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

function loadClosedDatesSidebar(onComplete) {
    return fetch(`${appUrl}/api/villas/${villaId}/all-reservations?year=all`)
        .then(res => res.json())
        .then(data => {
            masterReservationsList = sortReservationsByNearest(Array.isArray(data) ? data : []);
            filterClosedDatesSidebar();
            if (calendar) {
                calendar.refetchEvents();
            }
            requestAnimationFrame(() => {
                applyCalendarCellStyles(masterReservationsList);
            });
            if (typeof onComplete === 'function') onComplete();
        })
        .catch(err => console.error(err));
}

function filterClosedDatesSidebar() {
    const input = document.getElementById('sidebarSearchInput');
    const clearBtn = document.getElementById('clearSearchBtn');
    const query = input ? input.value.trim().toLowerCase() : '';
    
    if (clearBtn) {
        if (query) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    if (!query) {
        renderClosedDatesSidebar(masterReservationsList, false);
        return;
    }

    const monthsTR = ['', 'ocak', 'şubat', 'mart', 'nisan', 'mayıs', 'haziran', 'temmuz', 'ağustos', 'eylül', 'ekim', 'kasım', 'aralık'];

    const filtered = masterReservationsList.filter(item => {
        const startTr = formatDateTR(item.start_date).toLowerCase(); // '27.08.2026'
        const startDateIso = item.start_date.toLowerCase();          // '2026-08-27'
        const endTr = formatDateTR(item.end_date).toLowerCase();

        let labelText = 'Ev Sahibi';
        if (item.status === 'blocked') labelText = 'Kapalı / Bloke';
        else if (item.status === 'optional') labelText = 'Opsiyonlu';
        else if (item.agency_name || ['agency', 'acente', 'airbnb', 'booking', 'external_ical', 'generic_ical'].includes(item.source)) {
            const agencyTitle = item.agency_name || (item.customer_name && item.source === 'acente' ? item.customer_name : '');
            labelText = agencyTitle ? `Acente: ${agencyTitle}` : 'Acente';
        }
        labelText = labelText.toLowerCase();

        const startMonthIndex = parseInt(item.start_date.split('-')[1], 10);
        const monthName = monthsTR[startMonthIndex] || '';

        return startTr.includes(query) || 
               startDateIso.includes(query) || 
               endTr.includes(query) || 
               labelText.includes(query) || 
               monthName.includes(query) ||
               (item.customer_name && item.customer_name.toLowerCase().includes(query)) ||
               (item.agency_name && item.agency_name.toLowerCase().includes(query));
    });

    renderClosedDatesSidebar(filtered, true);
}

function clearSidebarSearch() {
    const input = document.getElementById('sidebarSearchInput');
    if (input) {
        input.value = '';
        filterClosedDatesSidebar();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    // Load full master sidebar list independently of calendar month
    loadClosedDatesSidebar();

    calendar = new FullCalendar.Calendar(calendarEl, {
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
            const dataToApply = (masterReservationsList && masterReservationsList.length > 0) ? masterReservationsList : lastCalendarData;
            if (dataToApply && dataToApply.length > 0) {
                requestAnimationFrame(() => {
                    applyCalendarCellStyles(dataToApply);
                });
            }
        },
        events: function(info, successCallback, failureCallback) {
            fetch(`${appUrl}/api/villas/${villaId}/calendar?start=${info.startStr}&end=${info.endStr}`)
                .then(res => res.json())
                .then(data => {
                    lastCalendarData = data;
                    const dataToApply = (masterReservationsList && masterReservationsList.length > 0) ? masterReservationsList : data;
                    const formattedEvents = dataToApply.map(item => ({
                        id: item.id,
                        start: item.start_date,
                        end: item.end_date,
                        display: 'background',
                        extendedProps: item
                    }));
                    successCallback(formattedEvents);
                    requestAnimationFrame(() => {
                        applyCalendarCellStyles(dataToApply);
                    });
                })
                .catch(err => {
                    if (masterReservationsList && masterReservationsList.length > 0) {
                        const formattedEvents = masterReservationsList.map(item => ({
                            id: item.id,
                            start: item.start_date,
                            end: item.end_date,
                            display: 'background',
                            extendedProps: item
                        }));
                        successCallback(formattedEvents);
                        requestAnimationFrame(() => {
                            applyCalendarCellStyles(masterReservationsList);
                        });
                    } else {
                        failureCallback(err);
                    }
                });
        },
        dateClick: function(info) {
            const todayStr = getTodayString();
            
            if (info.dateStr < todayStr) {
                cancelSelection();
                alert('Geçmiş tarihlere yeni rezervasyon veya kapatma işlemi yapılamaz.');
                return;
            }

            const clickedDate = info.dateStr;

            if (!pendingStartDate) {
                // 1. İLK TIKLAMA: Kutuyu hemen pembe renk yap
                pendingStartDate = clickedDate;
                highlightPendingStartDate(pendingStartDate);
                const banner = document.getElementById('selectionBanner');
                if (banner) {
                    const display = document.getElementById('selectedStartDisplay');
                    if (display) display.innerText = formatDateTR(pendingStartDate);
                    banner.classList.remove('hidden');
                }
            } else {
                if (clickedDate > pendingStartDate) {
                    // 2. İKİNCİ TIKLAMA: İkinci kutuyu ve aralığı pembe renk yap, 0.5 sn sonra rezervasyon formunu aç
                    const start = pendingStartDate;
                    const end = clickedDate;
                    
                    highlightRangeSelection(start, end);
                    
                    setTimeout(() => {
                        cancelSelection();
                        openModal(start, end);
                    }, 500);
                } else {
                    pendingStartDate = clickedDate;
                    highlightPendingStartDate(pendingStartDate);
                    const banner = document.getElementById('selectionBanner');
                    if (banner) {
                        const display = document.getElementById('selectedStartDisplay');
                        if (display) display.innerText = formatDateTR(pendingStartDate);
                    }
                }
            }
        },
        select: function(info) {
            const todayStr = getTodayString();
            
            // Check if selection starts in the past
            if (info.startStr < todayStr) {
                cancelSelection();
                alert('Geçmiş tarihlere yeni rezervasyon veya kapatma işlemi yapılamaz.');
                return;
            }

            const diffDays = Math.round((new Date(info.endStr) - new Date(info.startStr)) / (1000 * 60 * 60 * 24));
            
            if (diffDays > 1) {
                const endObj = new Date(info.endStr);
                endObj.setDate(endObj.getDate() - 1);
                const y = endObj.getFullYear();
                const m = String(endObj.getMonth() + 1).padStart(2, '0');
                const d = String(endObj.getDate()).padStart(2, '0');
                const endInclusive = `${y}-${m}-${d}`;

                highlightRangeSelection(info.startStr, endInclusive);

                setTimeout(() => {
                    cancelSelection();
                    openModal(info.startStr, info.endStr);
                }, 500);
            }
        },
        eventClick: function(info) {
            cancelSelection();
            const props = info.event.extendedProps;
            openModal(props.start_date, props.end_date, props);
        }
    });

    calendar.render();
});

function renderClosedDatesSidebar(events, isFiltered = false) {
    const listContainer = document.getElementById('closedDatesList');
    const badge = document.getElementById('closedCountBadge');
    if (!listContainer) return;

    const displayEvents = isFiltered ? events : (events || []).slice(0, 10);

    if (badge) {
        if (isFiltered) {
            badge.innerText = `${events.length} / ${masterReservationsList.length} kayıt`;
        } else {
            if (masterReservationsList.length > 10) {
                badge.innerText = `Son 10 / ${masterReservationsList.length} kayıt`;
            } else {
                badge.innerText = `${masterReservationsList.length} kayıt`;
            }
        }
    }

    if (!displayEvents || displayEvents.length === 0) {
        listContainer.innerHTML = `
            <div class="col-span-full text-center py-8 text-slate-400 text-xs">
                <i class="fa-regular fa-calendar-xmark text-2xl text-slate-300 mb-1"></i>
                <p>${isFiltered ? 'Arama kriterinize uygun tarih kaydı bulunamadı.' : 'Kayıtlı kapalı veya rezerve tarih bulunmuyor.'}</p>
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
        } else if (item.agency_name || ['agency', 'acente', 'airbnb', 'booking', 'external_ical', 'generic_ical'].includes(item.source)) {
            const agencyTitle = item.agency_name || (item.customer_name && item.source === 'acente' ? item.customer_name : '');
            labelText = agencyTitle ? `Acente: ${agencyTitle}` : 'Acente';
        } else {
            labelText = 'Ev Sahibi';
        }

        html += `
            <div class="p-3 rounded-xl border ${badgeClass} text-xs flex items-center justify-between gap-2 transition hover:shadow-sm">
                <div class="min-w-0 flex-1">
                    <div class="font-bold flex items-center space-x-1.5 truncate">
                        <i class="fa-solid ${icon} text-[11px] flex-shrink-0"></i>
                        <span class="truncate">${startTr} ➔ ${endTr}</span>
                    </div>
                    <div class="text-[11px] font-semibold mt-0.5 text-slate-600 truncate">${labelText}</div>
                </div>
                <div class="flex items-center space-x-1 flex-shrink-0">
                    <button onclick="openModalForReservation(${item.id})" class="px-2.5 py-1 bg-white hover:bg-indigo-600 hover:text-white text-indigo-700 font-bold rounded-lg border border-slate-200 text-[11px] shadow-sm transition" title="Detay / Düzenle">
                        Detay
                    </button>
                    <button onclick="quickDeleteReservation(${item.id})" class="px-2 py-1 bg-white hover:bg-rose-600 hover:text-white text-rose-700 font-bold rounded-lg border border-slate-200 text-[11px] shadow-sm transition" title="Tarihi Aç / Sil">
                        Aç / Sil
                    </button>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
}

function openModalForReservation(id) {
    const item = masterReservationsList.find(r => r.id == id);
    if (item) {
        cancelSelection();
        openModal(item.start_date, item.end_date, item);
    } else {
        alert('Rezervasyon detayına ulaşılamadı.');
    }
}

function quickDeleteReservation(id) {
    if (!confirm('Bu tarihi açmak / kaydı silmek istediğinize emin misiniz?')) return;
    fetch(`${appUrl}/api/reservations/${id}`, {
        method: 'DELETE'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            calendar.refetchEvents();
            loadClosedDatesSidebar();
        } else {
            alert('İşlem gerçekleştirilemedi.');
        }
    });
}

function openModal(startDate, endDate, existingProps = null) {
    document.getElementById('inputStartDate').value = startDate;
    document.getElementById('inputEndDate').value = endDate;
    document.getElementById('selectedDateText').innerText = `${formatDateTR(startDate)}  ➔  ${formatDateTR(endDate)}`;
    document.getElementById('modalError').classList.add('hidden');

    const modalButtons = document.getElementById('modalButtons');
    const btnSubmitText = document.getElementById('btnSubmitText');
    const btnCancelModal = document.getElementById('btnCancelModal');
    const waBtn = document.getElementById('whatsappBtn');

    if (existingProps) {
        document.getElementById('modalTitle').innerText = 'Rezervasyon Detayı / Düzenle';
        if (modalButtons) modalButtons.classList.remove('hidden');
        if (btnSubmitText) btnSubmitText.innerText = 'Güncelle';
        if (btnCancelModal) btnCancelModal.classList.add('hidden');

        document.getElementById('inputReservationId').value = existingProps.id;
        document.getElementById('inputCustomerName').value = existingProps.customer_name || '';
        document.getElementById('inputCustomerPhone').value = existingProps.customer_phone || '';
        document.getElementById('inputAgencyName').value = existingProps.agency_name || '';
        
        const bookingDateVal = existingProps.booking_date || (existingProps.created_at ? existingProps.created_at.substring(0, 10) : getTodayString());
        setBookingDateValue(bookingDateVal);

        document.getElementById('inputPrice').value = formatMoneyTR(existingProps.price);
        document.getElementById('inputDownPayment').value = formatMoneyTR(existingProps.down_payment);
        document.getElementById('inputRemainingPayment').value = formatMoneyTR(existingProps.remaining_payment);
        document.getElementById('inputNote').value = existingProps.note || '';

        let currentStatus = existingProps.status;
        if (existingProps.agency_name || ['agency', 'acente', 'airbnb', 'booking', 'external_ical', 'generic_ical'].includes(existingProps.source)) {
            currentStatus = 'agency';
        } else {
            currentStatus = 'booked';
        }

        const radioAgency = document.querySelector('input[name="status"][value="agency"]');
        const radioOwner = document.querySelector('input[name="status"][value="booked"]');
        const labelAgency = radioAgency ? radioAgency.closest('label') : null;
        const labelOwner = radioOwner ? radioOwner.closest('label') : null;

        if (labelAgency) labelAgency.classList.remove('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
        if (labelOwner) labelOwner.classList.remove('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
        if (radioAgency) radioAgency.disabled = false;
        if (radioOwner) radioOwner.disabled = false;

        const dateAgencyInput = document.getElementById('inputBookingDateAgency');
        const dateOwnerInput = document.getElementById('inputBookingDateOwner');
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

        toggleFields(currentStatus);

        if (existingProps.source) {
            document.getElementById('inputSource').value = existingProps.source;
        }

        if (currentStatus === 'booked' && existingProps.customer_phone) {
            let phone = String(existingProps.customer_phone).replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) {
                phone = '90' + phone.substring(1);
            } else if (phone.length === 10) {
                phone = '90' + phone;
            }
            if (phone && waBtn) {
                waBtn.href = `https://wa.me/${phone}`;
                waBtn.classList.remove('hidden');
            } else {
                if (waBtn) waBtn.classList.add('hidden');
            }
        } else {
            if (waBtn) waBtn.classList.add('hidden');
        }
    } else {
        document.getElementById('modalTitle').innerText = 'Yeni Tarih Aralığı İşlemi';
        if (btnSubmitText) btnSubmitText.innerText = 'Rezervasyon Kaydet';
        if (modalButtons) modalButtons.classList.remove('hidden');
        if (btnCancelModal) btnCancelModal.classList.remove('hidden');
        if (waBtn) waBtn.classList.add('hidden');

        const radioAgency = document.querySelector('input[name="status"][value="agency"]');
        const radioOwner = document.querySelector('input[name="status"][value="booked"]');
        const labelAgency = radioAgency ? radioAgency.closest('label') : null;
        const labelOwner = radioOwner ? radioOwner.closest('label') : null;

        if (labelAgency) labelAgency.classList.remove('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
        if (labelOwner) labelOwner.classList.remove('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
        if (radioAgency) radioAgency.disabled = false;
        if (radioOwner) radioOwner.disabled = false;

        const dateAgencyInput = document.getElementById('inputBookingDateAgency');
        const dateOwnerInput = document.getElementById('inputBookingDateOwner');
        if (dateAgencyInput) dateAgencyInput.disabled = true;
        if (dateOwnerInput) dateOwnerInput.disabled = true;

        document.getElementById('inputReservationId').value = '';
        document.getElementById('inputCustomerName').value = '';
        document.getElementById('inputCustomerPhone').value = '';
        document.getElementById('inputAgencyName').value = '';
        setBookingDateValue(getTodayString());
        document.getElementById('inputPrice').value = '';
        document.getElementById('inputDownPayment').value = '';
        document.getElementById('inputRemainingPayment').value = '';
        document.getElementById('inputNote').value = '';
        if (radioAgency) radioAgency.checked = true;
        toggleFields('agency');
    }

    document.getElementById('actionModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('actionModal').classList.add('hidden');
    cancelSelection();
}

function toggleFields(status) {
    const bookingFields = document.getElementById('bookingFields');
    const agencyNameBox = document.getElementById('agencyNameBox');
    const customerAndPriceFields = document.getElementById('customerAndPriceFields');
    const sourceSelect = document.getElementById('inputSource');

    if (status === 'agency') {
        bookingFields.classList.remove('hidden');
        agencyNameBox.classList.remove('hidden');
        customerAndPriceFields.classList.add('hidden');
    } else if (status === 'booked' || status === 'optional') {
        bookingFields.classList.remove('hidden');
        agencyNameBox.classList.add('hidden');
        customerAndPriceFields.classList.remove('hidden');

        // Ev Sahibi Rezervasyonu Kaynak Seçenekleri
        sourceSelect.innerHTML = `
            <option value="eski_misafir">Eski Misafir</option>
            <option value="instagram">İnstagram</option>
            <option value="google">Google</option>
            <option value="airbnb">Airbnb</option>
            <option value="vrbo">Vrbo</option>
            <option value="tavsiye">Tavsiye</option>
            <option value="acente">Acente</option>
        `;
    } else {
        bookingFields.classList.add('hidden');
        agencyNameBox.classList.add('hidden');
        customerAndPriceFields.classList.add('hidden');
    }
}

function handleReservationSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('reservationForm');
    const formData = new FormData(form);
    const status = formData.get('status');
    const resId = formData.get('reservation_id');

    if (status === 'agency') {
        formData.set('source', 'acente');
    }

    // Convert formatted money strings (e.g. 100.000 ₺ -> 100000) to clean numbers for DB
    const rawPrice = parseMoneyTR(document.getElementById('inputPrice').value);
    const rawDown = parseMoneyTR(document.getElementById('inputDownPayment').value);
    const rawRemaining = parseMoneyTR(document.getElementById('inputRemainingPayment').value);

    formData.set('price', rawPrice > 0 ? rawPrice : '');
    formData.set('down_payment', rawDown > 0 ? rawDown : '');
    formData.set('remaining_payment', rawRemaining > 0 ? rawRemaining : '');

    if (status === 'available') {
        if (!resId) {
            closeModal();
            return;
        }
        fetch(`${appUrl}/api/reservations/${resId}`, {
            method: 'DELETE'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeModal();
                calendar.refetchEvents();
                loadClosedDatesSidebar();
            } else {
                showModalError(data.message);
            }
        });
        return;
    }

    fetch(`${appUrl}/api/villas/${villaId}/reservations`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const rawPhone = formData.get('customer_phone') || document.getElementById('inputCustomerPhone')?.value || '';
            let phone = String(rawPhone).replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) {
                phone = '90' + phone.substring(1);
            } else if (phone.length === 10) {
                phone = '90' + phone;
            }

            const isEditing = !!(formData.get('reservation_id') || document.getElementById('inputReservationId')?.value);

            if (!isEditing && status === 'booked' && phone.length >= 10) {
                const rawName = formData.get('customer_name') || document.getElementById('inputCustomerName')?.value || '';
                const customerName = String(rawName).trim() || 'Misafir';
                const villaName = currentVillaData.name || 'Villa';
                const startDate = formatDateTR(formData.get('start_date') || '');
                const endDate = formatDateTR(formData.get('end_date') || '');
                const checkinTime = currentVillaData.checkin_time || '';
                const checkoutTime = currentVillaData.checkout_time || '';
                const priceVal = document.getElementById('inputPrice')?.value || '';
                const downVal = document.getElementById('inputDownPayment')?.value || '';
                const remainingVal = document.getElementById('inputRemainingPayment')?.value || '';
                const depositVal = currentVillaData.deposit_amount || '';
                const poolHeatingVal = currentVillaData.pool_heating_fee || '';

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

            closeModal();
            calendar.refetchEvents();
            loadClosedDatesSidebar();
        } else {
            showModalError(data.message);
        }
    })
    .catch(err => {
        console.error('Reservation Error:', err);
        showModalError(err.message || 'Bir ağ hatası oluştu.');
    });
}

function showModalError(msg) {
    const errDiv = document.getElementById('modalError');
    errDiv.innerText = msg;
    errDiv.classList.remove('hidden');
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
