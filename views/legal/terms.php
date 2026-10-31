<?php
declare(strict_types=1);

$pageTitle = 'Kullanım Koşulları (Terms of Service)';
$legalPageKey = 'terms';

ob_start();
?>
<div class="space-y-6">
    <div class="p-4 bg-slate-100 border border-slate-200 rounded-2xl text-slate-700 text-xs sm:text-sm leading-relaxed">
        TakvimTakip platformunu (web sitesi ve mobil uygulamalar) kullanarak aşağıdaki kullanım şartlarını ve sözleşme maddelerini kabul etmiş sayılırsınız. Lütfen hizmetlerimizi kullanmadan önce bu koşulları dikkatlice okuyunuz.
    </div>

    <section class="space-y-2">
        <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
            <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-black">1</span>
            <span>Hizmetin Tanımı ve Amacı</span>
        </h3>
        <p class="text-slate-600 pl-8">
            TakvimTakip; villa, yazlık, bungalov ve butik konaklama işletmecilerinin rezervasyonlarını, giriş-çıkış tarihlerini, müşteri iletişimlerini, ödemelerini ve iCal harici takvim senkronizasyonlarını tek bir merkezden yönetmelerini sağlayan bulut tabanlı bir yazılım (SaaS) hizmetidir.
        </p>
    </section>

    <section class="space-y-2">
        <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
            <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-black">2</span>
            <span>Hesap Güvenliği ve Kullanıcı Sorumlulukları</span>
        </h3>
        <p class="text-slate-600 pl-8">
            Kullanıcı, sisteme üye olurken verdiği bilgilerin doğruluğunu taahhüt eder. Giriş şifresi ve hesap güvenliğinin korunması kullanıcının kendi sorumluluğundadır. Hesaba eklenen konut bilgileri, rezervasyon kayıtları ve dış platformlardan (Airbnb, Booking vb.) bağlanan iCal bağlantılarının doğruluğu kullanıcı tarafından yönetilir.
        </p>
    </section>

    <section class="space-y-2">
        <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
            <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-black">3</span>
            <span>Hizmet Kesintisizliği ve Erişilebilirlik</span>
        </h3>
        <p class="text-slate-600 pl-8">
            Platform, kullanıcılarına kesintisiz ve yüksek performanslı hizmet sunabilmek adına %99.9 sunucu erişilebilirliği hedefiyle çalışmaktadır. Planlı sunucu bakımları, altyapı güncellemeleri veya mücbir sebepler haricinde sistem 7/24 erişilebilir durumdadır.
        </p>
    </section>

    <section class="space-y-2">
        <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
            <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-black">4</span>
            <span>Fikri ve Sınai Mülkiyet Hakları</span>
        </h3>
        <p class="text-slate-600 pl-8">
            TakvimTakip yazılımına ait kaynak kodlar, arayüz tasarımları, logolar, grafikler ve patentli özellikler TakvimTakip mülkiyetindedir. Kullanıcıya yalnızca hizmet süresi boyunca kişisel/işletmesel kullanım lisansı tanınır; sistem kodlarının kopyalanması veya tersine mühendislik uygulanması yasaktır.
        </p>
    </section>

    <section class="space-y-2">
        <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
            <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-black">5</span>
            <span>Hesabın Feshi ve İptal Şartları</span>
        </h3>
        <p class="text-slate-600 pl-8">
            Kullanıcı dilediği an hesabını kapatma ve sistemdeki verilerini silme hakkına sahiptir. Hizmet şartlarına aykırı, kötü niyetli veya sistem güvenliğini tehdit edici faaliyetlerde bulunan hesaplar TakvimTakip yönetimi tarafından askıya alınabilir veya sonlandırılabilir.
        </p>
    </section>

    <div class="mt-8 pt-6 border-t border-slate-200 text-xs text-slate-500">
        Sorularınız veya destek talepleriniz için <a href="<?= defined('APP_URL') ? APP_URL : '' ?>/iletisim" class="text-indigo-600 font-semibold underline">İletişim sayfamızı</a> ziyaret edebilirsiniz.
    </div>
</div>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/layout.php';
