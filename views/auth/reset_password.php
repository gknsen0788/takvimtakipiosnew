<?php
use App\Helpers;
$title = 'Yeni Şifre Belirle - TakvimTakip';
$hideNav = true;
require __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-md mx-auto my-10 bg-white p-8 rounded-2xl shadow-xl border border-slate-100">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 text-3xl mb-3 shadow-inner">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Yeni Şifre Belirleyin</h1>
        <p class="text-slate-500 text-sm mt-1">Lütfen hesabınız için yeni ve güçlü bir şifre girin.</p>
    </div>

    <form action="<?= APP_URL ?>/reset-password" method="POST" class="space-y-5">
        <?= Helpers::csrfField() ?>
        <input type="hidden" name="token" value="<?= Helpers::sanitize($token ?? '') ?>">

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Yeni Şifre</label>
            <input type="password" name="password" required minlength="6" placeholder="••••••••"
                   class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Yeni Şifre (Tekrar)</label>
            <input type="password" name="password_confirm" required minlength="6" placeholder="••••••••"
                   class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition">
        </div>

        <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base rounded-xl shadow-lg shadow-indigo-200 transition active:scale-[0.99]">
            Şifremi Güncelle
        </button>
    </form>

    <div class="mt-8 text-center border-t border-slate-100 pt-6 text-sm text-slate-600">
        <a href="<?= APP_URL ?>/login" class="font-bold text-indigo-600 hover:underline text-xs flex items-center justify-center space-x-1">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Giriş Ekranına Dön</span>
        </a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
