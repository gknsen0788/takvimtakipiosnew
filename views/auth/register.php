<?php
use App\Helpers;
$title = 'Yeni Hesap Oluştur - TakvimTakip';
$hideNav = true;
require __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-md mx-auto my-2 sm:my-6 bg-transparent sm:bg-white p-0 sm:p-8 rounded-none sm:rounded-2xl shadow-none sm:shadow-xl border-0 sm:border border-slate-100">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 text-2xl mb-2 shadow-inner">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Üye Hesabı Oluştur</h1>
        <p class="text-slate-500 text-sm mt-1">Villalarınızın müsaitlik takvimini hemen yönetmeye başlayın.</p>
    </div>

    <form action="<?= APP_URL ?>/register" method="POST" class="space-y-4">
        <?= Helpers::csrfField() ?>
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Ad Soyad</label>
            <input type="text" name="full_name" required
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition bg-white text-slate-800">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">E-posta Adresi</label>
            <input type="email" name="email" required
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition bg-white text-slate-800">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Telefon Numarası</label>
            <input type="tel" name="phone" required
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition bg-white text-slate-800">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Şifre</label>
            <input type="password" name="password" required minlength="6"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition bg-white text-slate-800">
        </div>

        <!-- Checkboxes for Privacy & KVKK -->
        <div class="space-y-2.5 pt-1">
            <label class="flex items-start space-x-2.5 text-xs text-slate-600 cursor-pointer select-none">
                <input type="checkbox" name="privacy_accepted" required class="mt-0.5 w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 shrink-0">
                <span><button type="button" onclick="openLegalModal('privacy')" class="font-bold text-indigo-600 hover:underline">Gizlilik Sözleşmesi</button>'ni okudum ve kabul ediyorum.</span>
            </label>

            <label class="flex items-start space-x-2.5 text-xs text-slate-600 cursor-pointer select-none">
                <input type="checkbox" name="kvkk_accepted" required class="mt-0.5 w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 shrink-0">
                <span><button type="button" onclick="openLegalModal('kvkk')" class="font-bold text-indigo-600 hover:underline">KVKK Aydınlatma Metni</button>'ni okudum ve onaylıyorum.</span>
            </label>
        </div>

        <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base rounded-xl shadow-lg shadow-indigo-200 transition active:scale-[0.99] mt-2">
            Ücretsiz Kaydol
        </button>
    </form>

    <div class="mt-6 text-center border-t border-slate-200/80 sm:border-slate-100 pt-4 text-sm text-slate-600">
        Zaten hesabınız var mı? 
        <a href="<?= APP_URL ?>/login" class="font-bold text-indigo-600 hover:underline">Giriş Yapın</a>
    </div>
</div>

<!-- Legal Pop-up Modal -->
<div id="legalModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-3xl max-w-2xl w-full p-6 space-y-4 max-h-[85vh] overflow-y-auto text-slate-800 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <h3 class="font-bold text-base text-slate-800" id="legalModalTitle">Yasal Bilgilendirme</h3>
            <button type="button" onclick="closeLegalModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center font-bold">&times;</button>
        </div>
        <div class="text-xs text-slate-600 leading-relaxed space-y-3" id="legalModalBody">
            <!-- Content filled by JS -->
        </div>
    </div>
</div>

<script>
function openLegalModal(type) {
    const modal = document.getElementById('legalModal');
    const title = document.getElementById('legalModalTitle');
    const body = document.getElementById('legalModalBody');

    if (type === 'privacy') {
        title.innerText = 'Gizlilik Sözleşmesi';
        body.innerHTML = `
            <div class="space-y-3">
                <h4 class="font-bold text-slate-800 text-sm">TakvimTakip'te Verileriniz Nasıl Korunuyor?</h4>
                <p>TakvimTakip olarak kişisel verilerinizin, müşteri bilgilerinizin ve işletme verilerinizin gizliliğine büyük önem veriyoruz. Sistemi tasarlarken yalnızca ihtiyaç duyduğumuz verileri toplamayı ve bu verilerin yalnızca gerekli olduğu amaçlarla kullanılmasını esas alıyoruz.</p>
                <p><strong>1. Yalnızca Gerekli Verileri Topluyoruz:</strong><br>TakvimTakip'i kullanabilmeniz ve rezervasyonlarınızı yönetebilmeniz için gerekli olan bilgileri topluyoruz. Bunlar; ad, e-posta adresi, telefon numarası, konut/işletme bilgileri ve rezervasyon yönetimi için sisteme eklediğiniz bilgiler olabilir. Gereksiz kişisel verilerin toplanmasından kaçınıyoruz.</p>
                <p><strong>2. Müşteri Bilgileriniz Gizli Kalır:</strong><br>TakvimTakip'e eklediğiniz müşterilerin ad, soyad, telefon numarası, iletişim bilgileri ve benzeri kişisel bilgileri herkese açık değildir. Bu bilgiler, siz ve yetkili kullanıcıların hesapları üzerinden erişebileceği şekilde sistem içerisinde güvenle tutulur.</p>
                <p><strong>3. Paylaşılan Takvimlerde Özel Bilgiler Gösterilmez:</strong><br>TakvimTakip üzerinden oluşturduğunuz iCal bağlantısı veya müsaitlik/paylaşım linklerinde, müşterilerinize ait kişisel bilgiler paylaşılmaz. Bu bağlantılar yalnızca paylaşılması gereken müsaitlik ve takvim bilgilerini göstermek amacıyla kullanılır.</p>
                <p><strong>4. Verilerinizi Pazarlama Amacıyla Satmıyoruz:</strong><br>Kişisel verileriniz reklam, pazarlama veya ticari amaçlarla üçüncü taraflara satılmaz veya kiralanmaz.</p>
                <p><strong>5. Verilerinize Erişim Kontrol Altındadır:</strong><br>TakvimTakip içerisindeki müşteri ve rezervasyon bilgileri, yalnızca ilgili hesap üzerinden yetkili kullanıcıların erişimine açıktır. Hesabınıza ait bilgilerin korunması için güvenli oturum açma ve erişim kontrol mekanizmaları kullanılmaktadır.</p>
                <p><strong>6. Verileriniz Yedeklenir ve Korunur:</strong><br>Sistemde bulunan rezervasyon ve işletme bilgilerinin kaybolmasını önlemek amacıyla düzenli veri yedekleme ve sistem güvenliği uygulamaları kullanılmaktadır. Böylece donanım arızası veya teknik sorunlar gibi durumlarda veri kaybı riskinin azaltılması hedeflenmektedir.</p>
                <p><strong>7. Veri Aktarımı Güvenli Bir Şekilde Gerçekleştirilir:</strong><br>TakvimTakip ile cihazınız arasındaki veri iletişiminde güvenli bağlantı teknolojileri kullanılır. Böylece sisteme giriş bilgilerinizin ve sistemle gerçekleştirdiğiniz veri alışverişinin güvenliği korunmaya çalışılır.</p>
                <p><strong>8. Verileriniz Üzerinde Kontrol:</strong><br>TakvimTakip hesabınızda bulunan işletme ve müşteri bilgilerinizi kolayca yönetebilirsiniz. İhtiyaç duyduğunuzda bilgilerinizi güncelleyebilir veya hesabınızdaki verileri silebilirsiniz.</p>
                <p><strong>9. KVKK'ya Uygunluk:</strong><br>TakvimTakip olarak kişisel verilerin işlenmesi süreçlerinde 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında gerekli yükümlülüklere uymayı hedefliyoruz.</p>
            </div>
        `;
    } else if (type === 'kvkk') {
        title.innerText = 'KVKK Aydınlatma Metni';
        body.innerHTML = `
            <p>6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca kişisel verileriniz işlenmektedir:</p>
            <p>Veri sorumlusu sıfatıyla TakvimTakip, kullanıcı verilerini kanunda belirtilen ilkeler doğrultusunda güvenli sunucularda saklar.</p>
            <p>Veri sahipleri diledikleri zaman hesap kapatma, veri güncelleme ve silme hakkına sahiptir.</p>
        `;
    }

    if (modal) modal.classList.remove('hidden');
}

function closeLegalModal() {
    const modal = document.getElementById('legalModal');
    if (modal) modal.classList.add('hidden');
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
