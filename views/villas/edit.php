<?php
use App\Helpers;
$title = Helpers::sanitize($villa['name']) . ' - Düzenle';
require __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-3xl mx-auto mb-10 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800"><?= Helpers::sanitize($villa['name']) ?></h1>
            <p class="text-slate-500 text-sm">Villa detaylarını ve fotoğraflarını güncelleyin.</p>
        </div>
        <a href="<?= APP_URL ?>/villas" class="text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 px-3 py-2 rounded-lg">&larr; Villalarım</a>
    </div>

    <!-- Single Cover Photo Section -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/90">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-1 flex items-center space-x-2">
            <i class="fa-solid fa-camera text-indigo-600"></i>
            <span>Villa Kapak Fotoğrafı</span>
        </h3>
        <p class="text-xs text-slate-500 mb-4">Konutunuz için 1 adet kapak fotoğrafı ekleyebilirsiniz. Yeni fotoğraf yüklendiğinde var olan kapak fotoğrafı otomatik güncellenir.</p>

        <?php if (!empty($villa['images'])): ?>
            <?php $coverImg = $villa['images'][0]; ?>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4 p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                <div class="w-40 h-28 rounded-lg overflow-hidden bg-slate-200 border border-slate-300 relative shrink-0">
                    <img src="<?= Helpers::sanitize($coverImg['image_url']) ?>" class="w-full h-full object-cover">
                    <span class="absolute top-1.5 left-1.5 bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow">Kapak Fotoğrafı</span>
                </div>
                <div class="flex-1 space-y-2">
                    <div class="text-xs font-bold text-slate-800">Mevcut Kapak Fotoğrafı</div>
                    <p class="text-[11px] text-slate-500">Değiştirmek için aşağıdan yeni bir fotoğraf seçebilir ya da mevcut fotoğrafı silebilirsiniz.</p>
                    <form action="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/delete-photo/<?= $coverImg['id'] ?>" method="POST" onsubmit="return confirm('Kapak fotoğrafını silmek istediğinize emin misiniz?');">
                        <?= Helpers::csrfField() ?>
                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-lg border border-rose-200 transition inline-flex items-center space-x-1.5">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                            <span>Fotoğrafı Sil</span>
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/upload-photo" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-3">
            <?= Helpers::csrfField() ?>
            <input type="file" name="photo" accept="image/*" required class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-sm transition">
                <?= !empty($villa['images']) ? 'Fotoğrafı Değiştir' : 'Fotoğraf Yükle' ?>
            </button>
        </form>
    </div>

    <!-- Edit Form -->
    <form action="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/edit" method="POST" class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/90 space-y-6">
        <?= Helpers::csrfField() ?>

        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Konut Adı *</label>
                    <input type="text" name="name" required value="<?= Helpers::sanitize($villa['name']) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Konut Tipi *</label>
                    <?php $propType = $villa['property_type'] ?? 'Villa'; ?>
                    <select name="property_type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none font-medium text-slate-800 bg-white">
                        <option value="Villa" <?= $propType === 'Villa' ? 'selected' : '' ?>>Villa</option>
                        <option value="Bungalov" <?= $propType === 'Bungalov' ? 'selected' : '' ?>>Bungalov</option>
                        <option value="Tiny House" <?= $propType === 'Tiny House' ? 'selected' : '' ?>>Tiny House</option>
                        <option value="Hotel" <?= $propType === 'Hotel' ? 'selected' : '' ?>>Hotel</option>
                        <option value="Apart" <?= $propType === 'Apart' ? 'selected' : '' ?>>Apart</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">İl *</label>
                    <input type="text" name="city" required value="<?= Helpers::sanitize($villa['city']) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">İlçe *</label>
                    <input type="text" name="district" required value="<?= Helpers::sanitize($villa['district']) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kısa Açıklama</label>
                <input type="text" name="short_description" value="<?= Helpers::sanitize($villa['short_description']) ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Detaylı Açıklama</label>
                <textarea name="detailed_description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none"><?= Helpers::sanitize($villa['detailed_description']) ?></textarea>
            </div>
        </div>

        <!-- Additional Information / Rules & Times -->
        <div class="space-y-4 pt-2">
            <h3 class="text-sm font-bold text-indigo-700 uppercase tracking-wider border-b border-indigo-50 pb-2">Konut ve Giriş / Çıkış Detayları</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Depozito</label>
                    <div class="relative flex items-center">
                        <input type="text" name="deposit_amount" value="<?= Helpers::formatMoneyInput($villa['deposit_amount'] ?? '') ?>" oninput="handleMoneyFormatInput(this)" placeholder="2.500" class="w-full pl-4 pr-7 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-medium outline-none">
                        <span class="absolute right-3 text-slate-400 font-bold text-sm pointer-events-none">₺</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Havuz Isıtma Ücreti</label>
                    <div class="relative flex items-center">
                        <input type="text" name="pool_heating_fee" value="<?= Helpers::formatMoneyInput($villa['pool_heating_fee'] ?? '') ?>" oninput="handleMoneyFormatInput(this)" placeholder="1.500" class="w-full pl-4 pr-7 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-medium outline-none">
                        <span class="absolute right-3 text-slate-400 font-bold text-sm pointer-events-none">₺</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Giriş Saati</label>
                    <input type="text" name="checkin_time" value="<?= Helpers::sanitize($villa['checkin_time'] ?? '') ?>" placeholder="Örn: 16:00" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Çıkış Saati</label>
                    <input type="text" name="checkout_time" value="<?= Helpers::sanitize($villa['checkout_time'] ?? '') ?>" placeholder="Örn: 10:00" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Temizlik Gün Sayısı</label>
                    <input type="number" name="cleaning_days" min="0" value="<?= $villa['cleaning_days'] ?? 0 ?>" placeholder="Örn: 5" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Temizlik Ücreti</label>
                    <div class="relative flex items-center">
                        <input type="text" name="cleaning_fee" value="<?= Helpers::formatMoneyInput($villa['cleaning_fee'] ?? '') ?>" oninput="handleMoneyFormatInput(this)" placeholder="1.500" class="w-full pl-4 pr-7 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-medium outline-none">
                        <span class="absolute right-3 text-slate-400 font-bold text-sm pointer-events-none">₺</span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Resim Google Drive Linki</label>
                <input type="url" name="google_drive_link" value="<?= Helpers::sanitize($villa['google_drive_link'] ?? '') ?>" placeholder="https://drive.google.com/drive/folders/..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm outline-none">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 pt-2">
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Kapasite</label>
                <input type="number" name="max_guests" min="1" value="<?= $villa['max_guests'] ?>" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Yatak Odası</label>
                <input type="number" name="bedrooms" min="1" value="<?= $villa['bedrooms'] ?>" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Banyo</label>
                <input type="number" name="bathrooms" min="1" value="<?= $villa['bathrooms'] ?>" class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm outline-none">
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 pt-6">
            <div class="flex items-center space-x-3">
                <button type="submit" class="py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow transition">
                    Değişiklikleri Kaydet
                </button>
                
                <button type="button" onclick="confirmDeleteVilla()" class="py-3 px-5 bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 font-bold text-sm rounded-xl border border-rose-200 shadow-xs transition flex items-center space-x-1.5" title="Konutu tamamen sil">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    <span>Konutu Sil</span>
                </button>
            </div>
        </div>
    </form>
</div>

<form id="deleteVillaForm" action="<?= APP_URL ?>/villas/<?= $villa['id'] ?>/delete" method="POST" class="hidden">
    <?= Helpers::csrfField() ?>
</form>

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

function confirmDeleteVilla() {
    if (confirm('Bu konutu ve bu konuta ait tüm kayıtları silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
        document.getElementById('deleteVillaForm').submit();
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
