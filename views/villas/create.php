<?php
use App\Helpers;
$title = 'Yeni Konut Ekle - VillaTakvim';
require __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-2xl mx-auto mb-10">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Yeni Konut Ekle</h1>
        <p class="text-slate-500 text-sm">Konutunuzun temel bilgilerini girerek takvimini hemen aktif edin.</p>
    </div>

    <form action="<?= APP_URL ?>/villas/new" method="POST" class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/90 space-y-6">
        <?= Helpers::csrfField() ?>

        <!-- Basic info -->
        <div class="space-y-4">
            <h3 class="text-sm font-bold text-indigo-700 uppercase tracking-wider border-b border-indigo-50 pb-2">Temel Bilgiler</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Konut Adı *</label>
                    <input type="text" name="name" required placeholder="Örn: Mavi Koy Lüks Konut" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Konut Tipi *</label>
                    <select name="property_type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none font-medium text-slate-800 bg-white">
                        <option value="Villa" selected>Villa</option>
                        <option value="Bungalov">Bungalov</option>
                        <option value="Tiny House">Tiny House</option>
                        <option value="Hotel">Hotel</option>
                        <option value="Apart">Apart</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">İl *</label>
                    <input type="text" name="city" required placeholder="Örn: Muğla" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">İlçe *</label>
                    <input type="text" name="district" required placeholder="Örn: Fethiye / Ölüdeniz" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kısa Açıklama</label>
                <input type="text" name="short_description" placeholder="Örn: Muhteşem deniz manzaralı, korunaklı havuzlu lüks konut" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
            </div>
        </div>

        <!-- Additional Information / Rules & Times -->
        <div class="space-y-4 pt-2">
            <h3 class="text-sm font-bold text-indigo-700 uppercase tracking-wider border-b border-indigo-50 pb-2">Konut ve Giriş / Çıkış Detayları</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Depozito</label>
                    <div class="relative flex items-center">
                        <input type="text" name="deposit_amount" oninput="handleMoneyFormatInput(this)" placeholder="2.500" class="w-full pl-4 pr-7 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-medium outline-none">
                        <span class="absolute right-3 text-slate-400 font-bold text-sm pointer-events-none">₺</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Havuz Isıtma Ücreti</label>
                    <div class="relative flex items-center">
                        <input type="text" name="pool_heating_fee" oninput="handleMoneyFormatInput(this)" placeholder="1.500" class="w-full pl-4 pr-7 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-medium outline-none">
                        <span class="absolute right-3 text-slate-400 font-bold text-sm pointer-events-none">₺</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Giriş Saati</label>
                    <input type="text" name="checkin_time" value="" placeholder="Örn: 16:00" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Çıkış Saati</label>
                    <input type="text" name="checkout_time" value="" placeholder="Örn: 10:00" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Temizlik Gün Sayısı</label>
                    <input type="number" name="cleaning_days" min="0" value="0" placeholder="Örn: 5" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Temizlik Ücreti</label>
                    <div class="relative flex items-center">
                        <input type="text" name="cleaning_fee" oninput="handleMoneyFormatInput(this)" placeholder="1.500" class="w-full pl-4 pr-7 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-medium outline-none">
                        <span class="absolute right-3 text-slate-400 font-bold text-sm pointer-events-none">₺</span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Resim Google Drive Linki</label>
                <input type="url" name="google_drive_link" placeholder="https://drive.google.com/drive/folders/..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
            </div>
        </div>

        <!-- Capacity & rules -->
        <div class="space-y-4 pt-2">
            <h3 class="text-sm font-bold text-indigo-700 uppercase tracking-wider border-b border-indigo-50 pb-2">Kapasite</h3>
            
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kapasite (Kişi)</label>
                    <input type="number" name="max_guests" min="1" value="4" class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Yatak Odası</label>
                    <input type="number" name="bedrooms" min="1" value="2" class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Banyo</label>
                    <input type="number" name="bathrooms" min="1" value="1" class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
            </div>
        </div>

        <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base rounded-xl shadow-lg transition active:scale-[0.99]">
            Konutu Kaydet & Takvimi Aç
        </button>
    </form>
</div>

<script>
function handleMoneyFormatInput(input) {
    let oldVal = input.value;
    let oldPos = input.selectionStart || 0;

    let raw = oldVal.replace(/[^0-9]/g, '');
    if (!raw || parseInt(raw, 10) === 0) {
        input.value = '';
    } else {
        input.value = new Intl.NumberFormat('tr-TR').format(parseInt(raw, 10));
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
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
