<?php
use App\Helpers;
use App\Auth;
$title = 'Konutlar - VillaTakvim';

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

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/90 shadow-sm mb-6">
    <div>
        <div class="flex items-center space-x-2 text-xs font-bold text-indigo-600 uppercase tracking-wider mb-1">
            <i class="fa-solid fa-house"></i>
            <span>Konut Portföyü & Yönetim</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Konutlar</h1>
        <p class="text-slate-500 text-sm mt-0.5">Yönettiğiniz tüm konutların listesi ve hızlı erişim menüsü.</p>
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

<?php if (empty($villas)): ?>
    <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm max-w-lg mx-auto">
        <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">
            <i class="fa-solid fa-house"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-800 mb-1">Henüz Konut Eklenmedi</h3>
        <p class="text-slate-500 text-sm mb-6">Sisteme hemen ilk konutunuzu ekleyin ve takviminizi 10 saniyede yönetmeye başlayın.</p>
        <a href="<?= APP_URL ?>/villas/new" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg transition">Yeni Konut Ekle</a>
    </div>
<?php else: ?>
    <!-- Search Bar & Add Villa Button Row -->
    <div class="mb-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 w-full">
        <!-- Search Input (Flex-1 narrows search bar on desktop from the right) -->
        <div class="relative flex-1 min-w-0">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <input type="text" id="propertySearchInput" oninput="filterProperties()" placeholder="Konut ara (Örn: Villa Armada, Kaş, Bungalov, Fethiye...)" class="w-full pl-10 pr-10 py-3 rounded-2xl border border-slate-200/90 text-sm font-medium outline-none focus:ring-2 focus:ring-indigo-500 bg-white shadow-xs placeholder-slate-400">
            <button id="clearPropertySearchBtn" onclick="clearPropertySearch()" class="hidden absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
        </div>

        <!-- Yeni Konut Ekle Button (Desktop Right Side) -->
        <a href="<?= APP_URL ?>/villas/new" class="inline-flex items-center justify-center px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl text-xs sm:text-sm shadow-md hover:shadow-lg transition space-x-2 shrink-0 cursor-pointer active:scale-95 whitespace-nowrap h-[46px]">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Yeni Konut Ekle</span>
        </a>
    </div>

    <!-- No Results Box -->
    <div id="noSearchPropertyResults" class="hidden bg-white rounded-2xl p-8 text-center border border-slate-200 shadow-sm max-w-md mx-auto mb-6">
        <i class="fa-solid fa-magnifying-glass text-3xl text-slate-300 mb-2"></i>
        <h3 class="text-base font-bold text-slate-800 mb-1">Aramaya Uygun Konut Bulunamadı</h3>
        <p class="text-slate-500 text-xs mb-4">Aradığınız kriterlere uyan konut adı, tipi veya lokasyonu bulunamadı.</p>
        <button onclick="clearPropertySearch()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">Aramayı Temizle</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($villas as $v): ?>
            <div class="property-card bg-white rounded-2xl border border-slate-200/90 shadow-sm hover:shadow-md transition flex flex-col overflow-hidden"
                 data-name="<?= Helpers::sanitize($v['name']) ?>"
                 data-type="<?= Helpers::sanitize($v['property_type'] ?? 'Villa') ?>"
                 data-location="<?= Helpers::sanitize($v['city']) ?> <?= Helpers::sanitize($v['district']) ?>">
                <!-- Cover Header -->
                <div class="h-44 bg-slate-100 relative">
                    <?php if (!empty($v['cover_image'])): ?>
                        <img src="<?= Helpers::sanitize($v['cover_image']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                            <i class="fa-solid fa-image text-4xl mb-1"></i>
                            <span class="text-xs">Fotoğraf Yok</span>
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-3 left-3">
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold shadow-sm bg-indigo-600/90 text-white backdrop-blur-sm">
                            <i class="fa-solid fa-house text-[10px] mr-1"></i><?= Helpers::sanitize($v['property_type'] ?? 'Villa') ?>
                        </span>
                    </div>
                    <div class="absolute top-3 right-3">
                        <button type="button" onclick="toggleVillaStatus(<?= $v['id'] ?>, this)" class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold shadow-md backdrop-blur-sm transition-all active:scale-95 <?= $v['status'] === 'active' ? 'bg-emerald-500/90 hover:bg-emerald-600 text-white' : 'bg-slate-700/90 hover:bg-slate-800 text-slate-200' ?>" data-status="<?= $v['status'] ?>" title="Konut Durumunu Değiştir (Aktif / Pasif)">
                            <span class="w-2 h-2 rounded-full <?= $v['status'] === 'active' ? 'bg-white animate-pulse' : 'bg-slate-400' ?> status-dot"></span>
                            <span class="status-text"><?= $v['status'] === 'active' ? 'Aktif' : 'Pasif' ?></span>
                            <i class="fa-solid <?= $v['status'] === 'active' ? 'fa-toggle-on text-sm' : 'fa-toggle-off text-sm text-slate-400' ?> status-icon"></i>
                        </button>
                    </div>
                    <?php if (Auth::isAdmin() && !empty($v['owner_name'])): ?>
                        <div class="absolute bottom-3 left-3">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold shadow-sm bg-slate-900/80 text-amber-300 backdrop-blur-sm" title="Ev Sahibi: <?= Helpers::sanitize($v['owner_name']) ?> (<?= Helpers::sanitize($v['owner_email'] ?? '') ?>)">
                                <i class="fa-solid fa-user-gear text-[10px] mr-1"></i><?= Helpers::sanitize($v['owner_name']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Content -->
                <div class="p-5 flex-grow flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 line-clamp-1"><?= Helpers::sanitize($v['name']) ?></h2>
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

                    <!-- Actions -->
                    <div class="mt-4 pt-2">
                        <div class="grid grid-cols-4 gap-1.5 text-center text-[11px]">
                            <a href="<?= APP_URL ?>/villas/<?= $v['id'] ?>/pricing" class="py-2 px-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition flex flex-col sm:flex-row items-center justify-center space-x-0 sm:space-x-1" title="Dönemsel Fiyatlar">
                                <i class="fa-solid fa-tag text-emerald-600 mb-0.5 sm:mb-0"></i>
                                <span>Fiyatlar</span>
                            </a>
                            <a href="<?= APP_URL ?>/villas/<?= $v['id'] ?>/ical" class="py-2 px-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition flex flex-col sm:flex-row items-center justify-center space-x-0 sm:space-x-1" title="iCal Entegrasyonu">
                                <i class="fa-solid fa-rotate text-blue-600 mb-0.5 sm:mb-0"></i>
                                <span>iCal</span>
                            </a>
                            <a href="<?= APP_URL ?>/villas/<?= $v['id'] ?>/edit" class="py-2 px-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition flex flex-col sm:flex-row items-center justify-center space-x-0 sm:space-x-1" title="Düzenle">
                                <i class="fa-solid fa-pen-to-square text-amber-600 mb-0.5 sm:mb-0"></i>
                                <span>Düzenle</span>
                            </a>
                            <?php if (!empty($v['slug']) && !str_starts_with($v['slug'], 'draft-')): ?>
                                <a href="<?= APP_URL ?>/villa/<?= $v['slug'] ?>" target="_blank" class="py-2 px-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition flex flex-col sm:flex-row items-center justify-center space-x-0 sm:space-x-1" title="Önizle (Müşteri Takvimi Sayfası)">
                                    <i class="fa-solid fa-eye text-indigo-600 mb-0.5 sm:mb-0"></i>
                                    <span>Önizle</span>
                                </a>
                            <?php else: ?>
                                <button type="button" onclick="alert('Lütfen önizleme yapmadan önce konut adını düzenleyin ve kaydedin.');" class="py-2 px-1 bg-slate-100 text-slate-400 font-semibold rounded-xl cursor-not-allowed opacity-60 transition flex flex-col sm:flex-row items-center justify-center space-x-0 sm:space-x-1" title="Önizleme yapmak için lütfen önce konut adını düzenleyiniz">
                                    <i class="fa-solid fa-eye text-slate-400 mb-0.5 sm:mb-0"></i>
                                    <span>Önizle</span>
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Buttons Grid: Villayı Kopyala & Fiyat Kopyala -->
                        <div class="grid grid-cols-2 gap-2 mt-2.5">
                            <a href="<?= APP_URL ?>/villas/<?= $v['id'] ?>/duplicate" onclick="return confirm('Bu konutun tüm bilgilerini (fiyatlar ve özellikler dahil) kopyalayarak yeni bir konut oluşturmak istediğinize emin misiniz?')" class="py-2 px-2 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 font-bold text-[11px] sm:text-xs rounded-xl border border-slate-200/80 shadow-sm transition text-center flex items-center justify-center space-x-1 active:scale-[0.99]" title="Fotoğraf Hariç Tüm Bilgileri Kopyala">
                                <i class="fa-solid fa-copy text-indigo-600"></i>
                                <span>Konut Kopyala</span>
                            </a>
                            <button type="button" onclick="openCopyPricesModal(<?= $v['id'] ?>, '<?= Helpers::sanitize(addslashes($v['name'])) ?>')" class="py-2 px-2 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 font-bold text-[11px] sm:text-xs rounded-xl border border-slate-200/80 shadow-sm transition text-center flex items-center justify-center space-x-1 active:scale-[0.99]" title="Başka Konuttan Fiyat Tarifesini Kopyala">
                                <i class="fa-solid fa-tags text-emerald-600"></i>
                                <span>Fiyat Kopyala</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
const appUrl = '<?= APP_URL ?>';

function toggleVillaStatus(villaId, btnEl) {
    fetch(`${appUrl}/api/villas/${villaId}/toggle-status`, {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btnEl.setAttribute('data-status', data.new_status);
            const dot = btnEl.querySelector('.status-dot');
            const text = btnEl.querySelector('.status-text');
            const icon = btnEl.querySelector('.status-icon');

            if (data.new_status === 'active') {
                btnEl.className = 'inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold shadow-md backdrop-blur-sm transition-all active:scale-95 bg-emerald-500/90 hover:bg-emerald-600 text-white';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-white animate-pulse status-dot';
                if (text) text.innerText = 'Aktif';
                if (icon) icon.className = 'fa-solid fa-toggle-on text-sm status-icon';
            } else {
                btnEl.className = 'inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold shadow-md backdrop-blur-sm transition-all active:scale-95 bg-slate-700/90 hover:bg-slate-800 text-slate-200';
                if (dot) dot.className = 'w-2 h-2 rounded-full bg-slate-400 status-dot';
                if (text) text.innerText = 'Pasif';
                if (icon) icon.className = 'fa-solid fa-toggle-off text-sm text-slate-400 status-icon';
            }
        } else {
            alert(data.message || 'Durum değiştirilemedi.');
        }
    })
    .catch(err => console.error(err));
}

function filterProperties() {
    const input = document.getElementById('propertySearchInput');
    const clearBtn = document.getElementById('clearPropertySearchBtn');
    const query = input ? input.value.trim().toLowerCase() : '';

    if (clearBtn) {
        if (query) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const cards = document.querySelectorAll('.property-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = (card.getAttribute('data-name') || '').toLowerCase();
        const type = (card.getAttribute('data-type') || '').toLowerCase();
        const location = (card.getAttribute('data-location') || '').toLowerCase();

        if (!query || name.includes(query) || type.includes(query) || location.includes(query)) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });

    const noResults = document.getElementById('noSearchPropertyResults');
    if (noResults) {
        if (visibleCount === 0 && query) {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }
    }
}

function clearPropertySearch() {
    const input = document.getElementById('propertySearchInput');
    if (input) {
        input.value = '';
        filterProperties();
    }
}

function copyPublicLink(url, btn) {
    const copySuccess = () => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check text-emerald-600 block sm:inline"></i> <span class="text-emerald-700 font-bold">Kopyalandı</span>';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(copySuccess).catch(() => fallbackCopy(url, copySuccess));
    } else {
        fallbackCopy(url, copySuccess);
    }
}

function fallbackCopy(url, callback) {
    const dummy = document.createElement('input');
    document.body.appendChild(dummy);
    dummy.value = url;
    dummy.select();
    document.execCommand('copy');
    document.body.removeChild(dummy);
    if (callback) callback();
}

function openCopyPricesModal(targetVillaId, targetVillaName) {
    const modal = document.getElementById('copyPricesModal');
    const form = document.getElementById('copyPricesForm');
    const nameSub = document.getElementById('targetVillaNameSub');
    const select = document.getElementById('sourceVillaSelect');

    if (modal && form) {
        form.action = `${appUrl}/villas/${targetVillaId}/copy-prices`;
        if (nameSub) nameSub.innerText = `Hedef: ${targetVillaName}`;

        if (select) {
            Array.from(select.options).forEach(opt => {
                if (opt.getAttribute('data-id') == targetVillaId) {
                    opt.disabled = true;
                } else {
                    opt.disabled = false;
                }
            });
            select.value = '';
        }

        modal.classList.remove('hidden');
    }
}

function closeCopyPricesModal() {
    const modal = document.getElementById('copyPricesModal');
    if (modal) modal.classList.add('hidden');
}
</script>

<!-- Fiyat Kopyala Modal Pop-up -->
<div id="copyPricesModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 border border-slate-100 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-tags text-base"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Dönemsel Fiyatları Kopyala</h3>
                    <p id="targetVillaNameSub" class="text-xs text-slate-500 truncate max-w-[240px]"></p>
                </div>
            </div>
            <button type="button" onclick="closeCopyPricesModal()" class="text-slate-400 hover:text-slate-600 p-1 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="copyPricesForm" action="" method="POST" class="space-y-4">
            <?= Helpers::csrfField() ?>
            
            <div class="bg-amber-50 border border-amber-200/80 p-3.5 rounded-xl text-amber-800 text-xs flex items-start space-x-2.5">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm mt-0.5 shrink-0"></i>
                <span>Seçtiğiniz kaynak konutun tüm dönemsel fiyat tarifesi bu konuta aktarılacaktır. Var olan eski fiyat tarifeleri temizlenip yenisi eklenecektir.</span>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Fiyatları Alınacak Kaynak Konut</label>
                <select name="source_villa_id" id="sourceVillaSelect" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 text-xs font-medium text-slate-700 bg-slate-50/60 outline-none">
                    <option value="" disabled selected class="text-slate-400">Seçiniz</option>
                    <?php foreach ($villas as $sv): ?>
                        <option value="<?= $sv['id'] ?>" data-id="<?= $sv['id'] ?>" class="text-slate-800 font-semibold">
                            <?= Helpers::sanitize($sv['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeCopyPricesModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                    Vazgeç
                </button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-200 transition active:scale-95 flex items-center space-x-1.5">
                    <i class="fa-solid fa-check text-sm"></i>
                    <span>Tamam</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
