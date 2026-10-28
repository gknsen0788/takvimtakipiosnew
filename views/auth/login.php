<?php
use App\Helpers;
$title = 'Giriş Yap - TakvimTakip';
$hideNav = true;
require __DIR__ . '/../layouts/header.php';
?>

<?php if (false): // Mobil Uygulama Yükleme Butonu (Logo tamamlanana kadar şimdilik gizli) ?>
<!-- PWA Mobil Uygulama Yükleme Butonu (Kutu Üstü Mavi Buton) -->
<div id="pwaInstallContainer" class="hidden max-w-md mx-auto mt-6 mb-4">
    <button id="pwaInstallBtn" type="button" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition active:scale-95 flex items-center justify-center space-x-2 cursor-pointer">
        <i class="fa-solid fa-mobile-screen-button text-base"></i>
        <span>Mobil Uygulamayı Yükle</span>
    </button>
</div>

<script>
let deferredPrompt;
const pwaContainer = document.getElementById('pwaInstallContainer');
const pwaBtn = document.getElementById('pwaInstallBtn');

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    if (pwaContainer) pwaContainer.classList.remove('hidden');
});

if (pwaBtn) {
    pwaBtn.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                pwaContainer.classList.add('hidden');
            }
            deferredPrompt = null;
        }
    });
}
</script>
<?php endif; ?>

<div class="w-full max-w-md mx-auto bg-transparent sm:bg-white p-0 sm:p-8 rounded-none sm:rounded-2xl shadow-none sm:shadow-xl border-0 sm:border border-slate-100">
    <div class="text-center mb-4 sm:mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-indigo-50 text-indigo-600 mb-2 sm:mb-3 shadow-inner overflow-hidden">
            <img src="<?= APP_URL ?>/uploads/logo-icon.jpg" alt="TakvimTakip" class="w-full h-full object-cover rounded-2xl" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='block';" />
            <i class="fa-solid fa-calendar-check text-2xl sm:text-3xl" style="display:none;"></i>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800">TakvimTakip'e Giriş Yap</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5 sm:mt-1">Defter ve Excel yerine, pratik ve modern takvim yönetimi</p>
    </div>

    <form action="<?= APP_URL ?>/login" method="POST" class="space-y-3.5 sm:space-y-5">
        <?= Helpers::csrfField() ?>
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-2">E-posta Adresi veya Cep Telefonu</label>
            <input type="text" name="email" required placeholder="E-posta adresi veya 05XX XXX XX XX"
                   class="w-full px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition bg-white text-slate-800">
        </div>

        <div>
            <div class="flex items-center justify-between mb-1 sm:mb-2">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Şifre</label>
                <a href="<?= APP_URL ?>/forgot-password" class="text-xs font-bold text-indigo-600 hover:underline">Şifremi Unuttum?</a>
            </div>
            <input type="password" name="password" required placeholder="••••••••"
                   class="w-full px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition bg-white text-slate-800">
        </div>

        <div class="flex items-center justify-between pt-0.5 sm:pt-1">
            <label class="flex items-center space-x-2 cursor-pointer select-none">
                <input type="checkbox" name="remember_me" value="1" checked
                       class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                <span class="text-xs font-semibold text-slate-700">Beni Hatırla</span>
            </label>
        </div>

        <button type="submit" class="w-full py-3 sm:py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm sm:text-base rounded-xl shadow-lg shadow-indigo-200 transition active:scale-[0.99]">
            Giriş Yap
        </button>
    </form>

    <div class="mt-4 sm:mt-8 text-center border-t border-slate-200/80 sm:border-slate-100 pt-3.5 sm:pt-6 text-xs sm:text-sm text-slate-600">
        Hesabınız yok mu? 
        <a href="<?= APP_URL ?>/register" class="font-bold text-indigo-600 hover:underline">Hemen Üye Olun</a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
