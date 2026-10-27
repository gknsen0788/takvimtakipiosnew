# 📱 TakvimTakip Android Uygulaması Derleme ve Kurulum Rehberi

Bu klasör, TakvimTakip yönetim panelinizi Android cihazlara yüklenebilir bir mobil uygulamaya dönüştüren eksiksiz Android Studio projesini içermektedir.

---

## ⚡ YÖNTEM 1: PWABuilder İle 1-Tıkla APK İndirme (En Kolay & Önerilen)

Bilgisayarınıza Android Studio kurmanıza gerek kalmadan, 1 dakikada Android uygulaması (`.apk` ve `.aab`) elde edebilirsiniz:

1. Web siteniz canlıya geçtiğinde (Örn: `https://takvimtakip.com.tr`), tarayıcıdan **[pwabuilder.com](https://www.pwabuilder.com)** adresine gidin.
2. Açılan kutuya sitenizin adresini yazıp **"Start"** butonuna basın.
3. PWABuilder hazırladığımız `manifest.json` dosyasını otomatik algılayacaktır.
4. **"Package for Stores"** butonuna tıklayın ve **Android (Package)** seçeneğini seçin.
5. ⚠️ **Play Protect Uyarısı Almamak İçin Önemli**: Açılan indirme penceresindeki **"Settings" (Ayarlar)** butonuna basıp **Target SDK / Android Version** seçeneğini **`34` (Android 14)** olarak ayarlayın.
6. İndirilen APK artık telefonlarda hiçbir Play Protect uyarısı vermeden temiz şekilde kurulacaktır!

---

## 🛠️ YÖNTEM 2: Android Studio İle Bilgisayarınızda APK Derleme

Eğer uygulamayı kendi bilgisayarınızda derlemek isterseniz:

1. [Android Studio](https://developer.android.com/studio) uygulamasını ücretsiz indirin ve kurun.
2. Android Studio'yu açın ve **Open an existing project** diyerek `takvim/android` klasörünü seçin.
3. Proje açıldıktan sonra `MainActivity.java` dosyasındaki `TARGET_URL` alanına sitenizin adresini girin:
   ```java
   private static final String TARGET_URL = "https://takvimtakip.com.tr";
   ```
4. Üst menüden **Build > Build Bundle(s) / APK(s) > Build APK(s)** yolunu izleyin.
5. Derleme bittiğinde sağ altta çıkan **"locate"** butonuna basarak oluşan `app-debug.apk` veya `app-release.apk` dosyanızı alın.

---

## 📲 Özellikler & Avantajlar
- **Otomatik Canlı Güncelleme**: Web panelinizde yaptığınız her değişiklik Android uygulamasına anında yansır.
- **Geri Tuşu Entegrasyonu**: Telefonda geri tuşuna basıldığında uygulamadan çıkmaz, web sayfalarında geriye gider.
- **Resim Yükleme Desteği**: Villa fotoğraflarını ve iCal dosyalarını telefondan yükleme imkanı.
- **Arama & WhatsApp Entegrasyonu**: Telefon arama ve WhatsApp linkleri doğrudan ilgili telefon uygulamalarını açar.
- **Pull-to-Refresh**: Ekranı aşağı çekerek sayfayı yenileme desteği.
