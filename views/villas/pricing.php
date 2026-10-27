<?php
use App\Helpers;
$title = Helpers::sanitize($villa['name']) . ' - Dönemsel Fiyatlandırma';
require __DIR__ . '/../layouts/header.php';
?>

<!-- Flatpickr Datepicker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/tr.js"></script>

<style>
    .flatpickr-calendar {
        width: 370px !important;
        border-radius: 1.25rem !important;
        box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.1) !important;
        border: 1px solid #cbd5e1 !important;
        padding: 12px !important;
        font-family: inherit !important;
    }
    .flatpickr-days, .dayContainer {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
    }
    .flatpickr-rContainer {
        width: 100% !important;
    }
    .flatpickr-day {
        max-width: 48px !important;
        height: 42px !important;
        line-height: 42px !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
    }
    .flatpickr-weekday {
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        color: #475569 !important;
    }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.endRange.nextMonthDay {
        background: #4f46e5 !important;
        border-color: #4f46e5 !important;
        font-weight: 700 !important;
        border-radius: 0.75rem !important;
    }
    .flatpickr-day:hover {
        background: #e0e7ff !important;
        border-color: #e0e7ff !important;
        color: #3730a3 !important;
        border-radius: 0.75rem !important;
    }
    .flatpickr-day.today {
        border-color: #4f46e5 !important;
        border-radius: 0.75rem !important;
    }
    .flatpickr-months .flatpickr-month {
        color: #1e293b !important;
        fill: #1e293b !important;
    }
    .flatpickr-current-month {
        font-size: 1.1rem !important;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months {
        font-weight: 700 !important;
    }
</style>

<div class="max-w-4xl mx-auto mb-10 space-y-6">
    <div>
        <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
            <a href="<?= APP_URL ?>/villas" class="hover:underline">Villalarım</a>
            <span>/</span>
            <a href="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/calendar" class="hover:underline"><?= Helpers::sanitize($villa['name']) ?></a>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Dönemsel Fiyatlandırma</h1>
    </div>

    <!-- Add Pricing Form -->
    <form action="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/pricing" method="POST" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/90 space-y-4">
        <?= Helpers::csrfField() ?>
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
            <i class="fa-solid fa-calendar-plus text-indigo-600"></i>
            <span>Yeni Fiyat Dönemi Ekle</span>
        </h3>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Başlangıç Tarihi</label>
                <div class="relative">
                    <input type="text" id="startDateInput" name="start_date" placeholder="GG.AA.YYYY" required class="w-full pl-8 sm:pl-9 pr-2 sm:pr-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50">
                    <i class="fa-regular fa-calendar absolute left-2.5 sm:left-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Bitiş Tarihi</label>
                <div class="relative">
                    <input type="text" id="endDateInput" name="end_date" placeholder="GG.AA.YYYY" required class="w-full pl-8 sm:pl-9 pr-2 sm:pr-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50">
                    <i class="fa-regular fa-calendar absolute left-2.5 sm:left-3 top-3 text-slate-400 text-xs pointer-events-none"></i>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Gecelik Fiyat (TL)</label>
                <div class="relative">
                    <input type="text" id="nightlyPriceDisplay" oninput="handleNightlyPriceInput(this)" placeholder="15.000 ₺" required class="w-full pl-8 sm:pl-9 pr-2 sm:pr-3 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-emerald-700 outline-none focus:ring-2 focus:ring-indigo-500 bg-emerald-50/30">
                    <input type="hidden" id="nightlyPriceHidden" name="nightly_price">
                    <i class="fa-solid fa-turkish-lira-sign absolute left-2.5 sm:left-3 top-3 text-emerald-600 text-xs pointer-events-none"></i>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Haftalık Fiyat (TL)</label>
                <div class="relative">
                    <input type="text" id="weeklyPriceDisplay" oninput="handleWeeklyPriceInput(this)" placeholder="105.000 ₺" class="w-full pl-8 sm:pl-9 pr-2 sm:pr-3 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-indigo-700 outline-none focus:ring-2 focus:ring-indigo-500 bg-indigo-50/30">
                    <input type="hidden" id="weeklyPriceHidden" name="weekly_price">
                    <i class="fa-solid fa-turkish-lira-sign absolute left-2.5 sm:left-3 top-3 text-indigo-600 text-xs pointer-events-none"></i>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition active:scale-[0.99]">
            Fiyat Dönemini Kaydet
        </button>
    </form>

    <!-- Pricing Periods List -->
    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-200/90">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Tanımlı Fiyat Dönemleri</h3>

        <?php if (empty($pricePeriods)): ?>
            <div class="text-center py-8 text-slate-400 text-xs">
                <i class="fa-solid fa-tag text-2xl text-slate-300 mb-1"></i>
                <p>Henüz tanımlanmış bir dönemsel fiyat bulunmuyor.</p>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border border-slate-100 overflow-hidden w-full">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 uppercase text-[10px] font-bold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-2 sm:px-4 whitespace-nowrap">Tarih Aralığı</th>
                            <th class="py-2.5 px-1.5 sm:px-4 whitespace-nowrap text-right sm:text-left">Gecelik</th>
                            <th class="py-2.5 px-1.5 sm:px-4 whitespace-nowrap text-right sm:text-left">Haftalık</th>
                            <th class="py-2.5 px-2 sm:px-4 text-right whitespace-nowrap">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($pricePeriods as $pp): ?>
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-2.5 px-2 sm:px-4 font-semibold text-slate-800 text-[11px] sm:text-xs whitespace-nowrap"><?= Helpers::formatDateTR($pp['start_date']) ?> ➔ <?= Helpers::formatDateTR($pp['end_date']) ?></td>
                                <td class="py-2.5 px-1.5 sm:px-4 font-bold text-emerald-600 text-[11px] sm:text-sm whitespace-nowrap text-right sm:text-left"><?= number_format((float)$pp['nightly_price'], 0, ',', '.') ?> TL</td>
                                <td class="py-2.5 px-1.5 sm:px-4 font-bold text-indigo-600 text-[11px] sm:text-sm whitespace-nowrap text-right sm:text-left"><?= number_format((float)($pp['weekly_price'] ?? ($pp['nightly_price'] * 7)), 0, ',', '.') ?> TL</td>
                                <td class="py-2.5 px-2 sm:px-4 text-right whitespace-nowrap">
                                    <form action="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/pricing/delete/<?= $pp['id'] ?>" method="POST" onsubmit="return confirm('Bu fiyat dönemini silmek istediğinize emin misiniz?');" class="inline">
                                        <?= Helpers::csrfField() ?>
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs bg-rose-50 hover:bg-rose-100 px-2 py-1 rounded-lg transition">Sil</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function parseMoneyTR(val) {
    if (!val) return 0;
    let clean = String(val).replace(/[^0-9]/g, '');
    return parseFloat(clean) || 0;
}

function handleNightlyPriceInput(input) {
    let oldVal = input.value;
    let oldPos = input.selectionStart || 0;

    let raw = parseMoneyTR(oldVal);
    const hiddenNightly = document.getElementById('nightlyPriceHidden');
    const weeklyDisplay = document.getElementById('weeklyPriceDisplay');
    const hiddenWeekly = document.getElementById('weeklyPriceHidden');

    if (raw > 0) {
        input.value = new Intl.NumberFormat('tr-TR').format(raw);
        hiddenNightly.value = raw;

        let rawWeekly = Math.round(raw * 7);
        weeklyDisplay.value = new Intl.NumberFormat('tr-TR').format(rawWeekly);
        hiddenWeekly.value = rawWeekly;
    } else {
        input.value = '';
        hiddenNightly.value = '';
        weeklyDisplay.value = '';
        hiddenWeekly.value = '';
    }

    let newVal = input.value;
    let addedChars = newVal.length - oldVal.length;
    let newPos = Math.max(0, oldPos + addedChars);
    if (input.setSelectionRange && document.activeElement === input) {
        try {
            input.setSelectionRange(newPos, newPos);
        } catch (e) {}
    }
}

function handleWeeklyPriceInput(input) {
    let oldVal = input.value;
    let oldPos = input.selectionStart || 0;

    let rawWeekly = parseMoneyTR(oldVal);
    const hiddenWeekly = document.getElementById('weeklyPriceHidden');
    const nightlyDisplay = document.getElementById('nightlyPriceDisplay');
    const hiddenNightly = document.getElementById('nightlyPriceHidden');

    if (rawWeekly > 0) {
        input.value = new Intl.NumberFormat('tr-TR').format(rawWeekly);
        hiddenWeekly.value = rawWeekly;

        let rawNightly = Math.round(rawWeekly / 7);
        nightlyDisplay.value = new Intl.NumberFormat('tr-TR').format(rawNightly);
        hiddenNightly.value = rawNightly;
    } else {
        input.value = '';
        hiddenWeekly.value = '';
        nightlyDisplay.value = '';
        hiddenNightly.value = '';
    }

    let newVal = input.value;
    let addedChars = newVal.length - oldVal.length;
    let newPos = Math.max(0, oldPos + addedChars);
    if (input.setSelectionRange && document.activeElement === input) {
        try {
            input.setSelectionRange(newPos, newPos);
        } catch (e) {}
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let endPickerInstance;

    const startPickerInstance = flatpickr("#startDateInput", {
        locale: 'tr',
        altInput: true,
        altFormat: "d.m.Y",
        dateFormat: "Y-m-d",
        minDate: "today",
        disableMobile: true,
        monthSelectorType: "static",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0 && endPickerInstance) {
                endPickerInstance.set('minDate', selectedDates[0]);
                
                // 0.2 saniye (200ms) sonra bitiş tarihi takvimini otomatik aç
                setTimeout(function() {
                    endPickerInstance.open();
                }, 200);
            }
        }
    });

    endPickerInstance = flatpickr("#endDateInput", {
        locale: 'tr',
        altInput: true,
        altFormat: "d.m.Y",
        dateFormat: "Y-m-d",
        minDate: "today",
        disableMobile: true,
        monthSelectorType: "static"
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
