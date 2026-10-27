<?php
use App\Helpers;
$title = Helpers::sanitize($villa['name']) . ' - iCal Senkronizasyonu';
require __DIR__ . '/../layouts/header.php';

$exportUrl = APP_URL . '/api/calendar/' . $villa['ical_export_token'] . '.ics';
?>

<div class="max-w-4xl mx-auto mb-10 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="<?= APP_URL ?>/villas" class="hover:underline">Villalarım</a>
                <span>/</span>
                <a href="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/calendar" class="hover:underline"><?= Helpers::sanitize($villa['name']) ?></a>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">iCal / ICS Takvim Senkronizasyonu</h1>
            <p class="text-slate-500 text-sm">Dış siteler ve acentelerle çift yönlü otomatik takvim senkronizasyonu.</p>
        </div>
        <a href="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/calendar" class="text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 px-3 py-2 rounded-lg">&larr; Takvime Dön</a>
    </div>

    <!-- Export Section -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/90 space-y-3">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
            <i class="fa-solid fa-cloud-arrow-up text-indigo-600"></i>
            <span>iCal Export Linkiniz (Dışarıya Müsaitlik Verin)</span>
        </h3>
        <p class="text-xs text-slate-500">
            Kendi web siteniz, acenteniz veya başka platformlar bu linki çekerek villanızın dolu/müsait tarihlerini otomatik güncelleyebilir.
            <strong class="text-rose-600">Özel müşteri bilgileri (ad, telefon, fiyat) bu linkte KESİNLİKLE görünmez.</strong>
        </p>

        <div class="flex items-center space-x-2 pt-1">
            <input type="text" readonly id="exportUrlInput" value="<?= $exportUrl ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-700 text-xs font-mono select-all">
            <button onclick="copyExportUrl()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs shadow transition flex-shrink-0 flex items-center space-x-1">
                <i class="fa-regular fa-copy"></i>
                <span id="copyBtnText">Kopyala</span>
            </button>
        </div>
    </div>

    <!-- Import Section -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/90 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                <i class="fa-solid fa-cloud-arrow-down text-blue-600"></i>
                <span>iCal Import (Airbnb, Booking.com Takvimlerini Çekin)</span>
            </h3>
            <form action="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/ical/sync-all" method="POST">
                <?= Helpers::csrfField() ?>
                <button type="submit" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold rounded-lg text-xs transition flex items-center space-x-1">
                    <i class="fa-solid fa-rotate"></i>
                    <span>Şimdi Senkronize Et</span>
                </button>
            </form>
        </div>

        <!-- Add Import Form -->
        <form action="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/ical/import" method="POST" class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
            <?= Helpers::csrfField() ?>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kaynak Adı</label>
                    <input type="text" name="name" required placeholder="Örn: Airbnb Mavi Koy" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Platform Türü</label>
                    <select name="source_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="airbnb">Airbnb</option>
                        <option value="booking">Booking.com</option>
                        <option value="generic_ical">Diğer iCal Bağlantısı</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">iCal (.ics) URL</label>
                    <input type="url" name="ical_url" required placeholder="https://www.airbnb.com/calendar/ical/..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">
                Dış Takvim Bağlantısını Ekle
            </button>
        </form>

        <!-- External Calendars List -->
        <?php if (empty($externalCalendars)): ?>
            <div class="text-center py-6 text-slate-400 text-xs">
                <p>Henüz harici bir iCal takvimi bağlanmamış.</p>
            </div>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($externalCalendars as $cal): ?>
                    <div class="p-3 bg-white rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                        <div>
                            <div class="font-bold text-slate-800 flex items-center space-x-2">
                                <span><?= Helpers::sanitize($cal['name']) ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 uppercase"><?= $cal['source_type'] ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $cal['sync_status'] === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                    <?= $cal['sync_status'] === 'success' ? 'Başarılı' : 'Hata' ?>
                                </span>
                            </div>
                            <div class="text-slate-400 text-[11px] font-mono truncate max-w-md mt-0.5"><?= Helpers::sanitize($cal['ical_url']) ?></div>
                            <?php if (!empty($cal['last_synced_at'])): ?>
                                <div class="text-[10px] text-slate-400 mt-0.5">Son senkronizasyon: <?= $cal['last_synced_at'] ?></div>
                            <?php endif; ?>
                            <?php if (!empty($cal['last_error'])): ?>
                                <div class="text-[11px] text-rose-600 mt-0.5">Hata mesajı: <?= Helpers::sanitize($cal['last_error']) ?></div>
                            <?php endif; ?>
                        </div>

                        <form action="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/ical/delete/<?= $cal['id'] ?>" method="POST" onsubmit="return confirm('Bu takvim bağlantısını silmek istediğinize emin misiniz?');">
                            <?= Helpers::csrfField() ?>
                            <button type="submit" class="px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg text-xs transition">Bağlantıyı Sil</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyExportUrl() {
    const input = document.getElementById('exportUrlInput');
    input.select();
    document.execCommand('copy');
    const btnText = document.getElementById('copyBtnText');
    btnText.innerText = 'Kopyalandı!';
    setTimeout(() => { btnText.innerText = 'Kopyala'; }, 2000);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
