<?php
use App\Auth;
use App\Helpers;

$title = "Hesap Bilgilerim - TakvimTakip";

$userObj = $profileUser ?? Auth::user();

// Calculate license status and remaining days
$today = date('Y-m-d');
$expDate = $userObj['license_expires_at'] ?? null;
$userRole = $userObj['role'] ?? 'owner';
$remDays = 0;
$licenseBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-700"><i class="fa-solid fa-clock-rotate-left mr-1"></i> Süresi Doldu</span>';

if ($expDate) {
    $remDays = (int)floor((strtotime($expDate) - strtotime($today)) / 86400);
    if ($remDays > 10) {
        $licenseBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700"><i class="fa-solid fa-circle-check mr-1"></i> Aktif (' . date('d.m.Y', strtotime($expDate)) . ')</span>';
    } elseif ($remDays >= 0) {
        $licenseBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-700"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Son ' . $remDays . ' Gün (' . date('d.m.Y', strtotime($expDate)) . ')</span>';
    } else {
        $licenseBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-700"><i class="fa-solid fa-clock-rotate-left mr-1"></i> Süresi Doldu</span>';
    }
}

$isDemo = ($userRole === 'demo');
$is7DaysOrLess = ($expDate !== null && $remDays <= 7);

$showActionButton = (!Auth::isAdmin() && ($isDemo || $is7DaysOrLess));
$remDaysCount = max(0, $remDays);
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

<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-user-gear text-indigo-600"></i>
                Hesap Bilgilerim
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-1">Kişisel bilgilerinizi görüntüleyin, güncelleyin ve hesap şifrenizi değiştirin.</p>
        </div>
    </div>

    <!-- Account Overview Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6">
        <div class="flex flex-col md:flex-row items-start gap-4 sm:gap-6">
            <!-- Avatar Circle -->
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl sm:text-3xl font-extrabold shadow-md shrink-0">
                <?= mb_strtoupper(mb_substr($userObj['full_name'] ?? 'K', 0, 1, 'UTF-8'), 'UTF-8') ?>
            </div>
            <!-- Profile Info Grid -->
            <div class="flex-grow w-full space-y-4">
                <div class="pb-4 border-b border-slate-100">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800 truncate"><?= Helpers::sanitize($userObj['full_name']) ?></h2>
                    <div class="text-xs text-slate-500 flex flex-wrap items-center gap-x-2 gap-y-1 mt-1">
                        <span class="truncate"><i class="fa-regular fa-envelope text-slate-400 mr-1"></i><?= Helpers::sanitize($userObj['email']) ?></span>
                        <?php if (!empty($userObj['phone'])): ?>
                            <span class="hidden sm:inline">•</span>
                            <span><i class="fa-solid fa-phone text-slate-400 mr-1"></i><?= Helpers::sanitize($userObj['phone']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-400 block font-medium mb-0.5">Kayıt Tarihi</span>
                        <span class="font-bold text-slate-700"><?= !empty($userObj['created_at']) ? date('d.m.Y H:i', strtotime($userObj['created_at'])) : '-' ?></span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-400 block font-medium mb-0.5">Lisans Bitiş Tarihi</span>
                        <span class="font-bold text-slate-700"><?= !empty($expDate) ? date('d.m.Y', strtotime($expDate)) : '-' ?></span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-400 block font-medium mb-0.5">Kalan Lisans Süresi</span>
                        <span class="font-bold <?= $remDays > 10 ? 'text-emerald-600' : ($remDays >= 0 ? 'text-amber-600' : 'text-rose-600') ?>">
                            <?= $remDays > 0 ? $remDays . ' Gün' : ($remDays === 0 ? 'Bugün Son Gün' : 'Süresi Doldu') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forms Section: Two Columns for Profile Info and Password Change -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Form 1: Profil Bilgilerini Güncelle -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
            <div class="flex items-center space-x-3 pb-4 mb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Profil Bilgileri</h3>
                    <p class="text-xs text-slate-500">Ad soyad, e-posta ve telefon numaranızı güncelleyin.</p>
                </div>
            </div>

            <form action="<?= APP_URL ?>/profile" method="POST" class="space-y-4 flex-grow flex flex-col justify-between">
                <?= Helpers::csrfField() ?>
                <input type="hidden" name="action" value="update_profile">

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ad Soyad <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i class="fa-solid fa-user absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <input type="text" name="full_name" required value="<?= Helpers::sanitize($userObj['full_name']) ?>" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">E-posta Adresi <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <input type="email" name="email" required value="<?= Helpers::sanitize($userObj['email']) ?>" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Cep Telefonu Numarası</label>
                        <div class="relative">
                            <i class="fa-solid fa-phone absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <input type="tel" name="phone" value="<?= Helpers::sanitize($userObj['phone'] ?? '') ?>" placeholder="05XX XXX XX XX" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100">
                    <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Profil Bilgilerini Güncelle</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Form 2: Şifre Değiştir -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
            <div class="flex items-center space-x-3 pb-4 mb-4 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl text-white flex items-center justify-center font-bold text-lg" style="background-color: #febe05;">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Şifre Değiştir</h3>
                    <p class="text-xs text-slate-500">Güvenliğiniz için güçlü bir şifre belirleyin.</p>
                </div>
            </div>

            <form action="<?= APP_URL ?>/profile" method="POST" class="space-y-4 flex-grow flex flex-col justify-between">
                <?= Helpers::csrfField() ?>
                <input type="hidden" name="action" value="update_password">

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mevcut Şifreniz <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i class="fa-solid fa-key absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <input type="password" name="current_password" required placeholder="Mevcut şifrenizi giriniz" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Yeni Şifre <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <input type="password" name="new_password" required minlength="6" placeholder="En az 6 karakter" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Yeni Şifre (Tekrar) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <i class="fa-solid fa-shield-halved absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <input type="password" name="confirm_password" required minlength="6" placeholder="Yeni şifreyi tekrar giriniz" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100">
                    <button type="submit" style="background-color: #febe05;" class="w-full py-2.5 px-4 text-white hover:brightness-95 font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-key"></i>
                        <span>Şifreyi Güncelle</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Action Row: Hesabı Kalıcı Olarak Sil (Left) & Abonelik Butonu (Right) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pt-2">
        <div>
            <?php if (!Auth::isAdmin()): ?>
                <form action="<?= APP_URL ?>/profile/delete-account" method="POST" onsubmit="return confirm('Hesabınızı ve tüm verilerinizi kalıcı olarak silmek istediğinize emin misiniz?\n\nBu işlem geri alınamaz!');" class="w-full sm:w-auto">
                    <?= Helpers::csrfField() ?>
                    <button type="submit" class="w-full sm:w-auto h-11 px-5 text-xs font-bold rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 transition flex items-center justify-center space-x-2 shadow-sm">
                        <i class="fa-solid fa-user-xmark"></i>
                        <span>Hesabı Kalıcı Olarak Sil</span>
                    </button>
                </form>
            <?php endif; ?>
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
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
