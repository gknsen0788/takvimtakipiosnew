<?php
use App\Helpers;
$title = 'Şifremi Unuttum - TakvimTakip';
$hideNav = true;
require __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-md mx-auto my-2 sm:my-10 bg-transparent sm:bg-white p-0 sm:p-8 rounded-none sm:rounded-2xl shadow-none sm:shadow-xl border-0 sm:border border-slate-100">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 text-3xl mb-3 shadow-inner">
            <i class="fa-solid fa-key"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Şifremi Unuttum</h1>
        <p class="text-slate-500 text-sm mt-1">Hesabınıza ait e-posta adresini veya cep telefonunu girerek şifrenizi sıfırlayabilirsiniz.</p>
    </div>

    <form action="<?= APP_URL ?>/forgot-password" method="POST" class="space-y-5">
        <?= Helpers::csrfField() ?>
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">E-posta Adresi veya Cep Telefonu</label>
            <input type="text" name="email_or_phone" required placeholder="ornek@domain.com veya 05XX XXX XX XX"
                   value="<?= Helpers::sanitize($_POST['email_or_phone'] ?? '') ?>"
                   class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition bg-white text-slate-800">
        </div>

        <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base rounded-xl shadow-lg shadow-indigo-200 transition active:scale-[0.99]">
            Şifre Sıfırlama E-Postası Gönder
        </button>
    </form>

    <div class="mt-8 text-center border-t border-slate-200/80 sm:border-slate-100 pt-6 text-sm text-slate-600 flex items-center justify-between">
        <a href="<?= APP_URL ?>/login" class="font-semibold text-indigo-600 hover:underline text-xs flex items-center space-x-1">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Giriş Sayfasına Dön</span>
        </a>
        <a href="<?= APP_URL ?>/register" class="font-semibold text-slate-600 hover:underline text-xs">
            Hemen Üye Ol
        </a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
