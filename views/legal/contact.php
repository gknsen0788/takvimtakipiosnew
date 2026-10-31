<?php
declare(strict_types=1);

$pageTitle = 'İletişim Bilgileri';
$legalPageKey = 'contact';

ob_start();
?>
<div class="space-y-6">
    <div class="p-4 bg-emerald-50/70 border border-emerald-100 rounded-2xl text-emerald-900 text-xs sm:text-sm leading-relaxed">
        TakvimTakip destek ekibine haftanın 7 günü 09:00 - 22:00 saatleri arasında ulaşabilir; teknik destek, üyelik, takvim kurulumu ve önerileriniz için bizimle iletişime geçebilirsiniz.
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
        <!-- Telefon -->
        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 flex items-start space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-phone"></i>
            </div>
            <div>
                <h4 class="font-bold text-slate-900 text-sm">İrtibat Hattı</h4>
                <p class="text-xs text-slate-500 mt-0.5">Doğrudan çağrı merkezi ve arama</p>
                <a href="tel:05437161546" class="inline-block mt-2 font-black text-emerald-600 hover:text-emerald-700 text-base">
                    0543 716 15 46
                </a>
            </div>
        </div>

        <!-- WhatsApp -->
        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 flex items-start space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-2xl shrink-0 shadow-md shadow-emerald-500/20">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <div>
                <h4 class="font-bold text-slate-900 text-sm">WhatsApp Destek</h4>
                <p class="text-xs text-slate-500 mt-0.5">Anlık mesajlaşma ve hızlı destek</p>
                <a href="https://wa.me/905437161546?text=Merhaba,%20TakvimTakip%20hakk%C4%B1nda%20bilgi%20almak%20istiyorum." target="_blank" rel="noopener noreferrer" class="inline-block mt-2 font-bold text-emerald-600 hover:text-emerald-700 text-xs underline">
                    WhatsApp ile Mesaj Gönder &rarr;
                </a>
            </div>
        </div>

        <!-- E-posta -->
        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 flex items-start space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-envelope"></i>
            </div>
            <div>
                <h4 class="font-bold text-slate-900 text-sm">E-Posta Desteği</h4>
                <p class="text-xs text-slate-500 mt-0.5">Resmi yazışmalar ve kurumsal talepler</p>
                <a href="mailto:destek@takvimtakip.com.tr" class="inline-block mt-2 font-bold text-indigo-600 hover:text-indigo-700 text-xs">
                    destek@takvimtakip.com.tr
                </a>
            </div>
        </div>

        <!-- Kurumsal Altyapı -->
        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60 flex items-start space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-indigo-600/20">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Altyapı & Güvence</h4>
                <p class="text-xs text-slate-500 mt-0.5">Kurumsal villa kiralama ekosistemi</p>
                <a href="https://www.trendvillas.net" target="_blank" rel="noopener noreferrer" class="inline-block mt-2 font-bold text-indigo-600 hover:text-indigo-700 text-xs underline">
                    www.trendvillas.net &rarr;
                </a>
            </div>
        </div>
    </div>

    <div class="p-4 bg-slate-100 rounded-2xl text-xs text-slate-600 space-y-1">
        <p><strong>Çalışma Saatleri:</strong> Pazartesi - Pazar (Haftanın 7 Günü): 09:00 - 22:00</p>
        <p><strong>Adres / Hizmet Bölgesi:</strong> Fethiye, Kaş, Kalkan ve tüm Türkiye konaklama işletmeleri geneli.</p>
    </div>
</div>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/layout.php';
