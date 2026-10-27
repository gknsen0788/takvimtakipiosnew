package com.takvimtakip.app2026;

import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.Color;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.view.View;
import android.webkit.JavascriptInterface;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Toast;

import androidx.activity.OnBackPressedCallback;
import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.core.view.WindowInsetsControllerCompat;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

public class MainActivity extends AppCompatActivity {

    private WebView webView;
    private SwipeRefreshLayout swipeRefreshLayout;
    private ValueCallback<Uri[]> filePathCallback;
    private ActivityResultLauncher<Intent> fileChooserLauncher;

    private static final String APP_URL = "https://takvimtakip.com.tr/login";

    // Javascript Interface for Web-to-Android Communication
    public class WebAppInterface {
        @JavascriptInterface
        public void setSwipeRefreshEnabled(boolean enabled) {
            runOnUiThread(() -> {
                if (swipeRefreshLayout != null) {
                    swipeRefreshLayout.setEnabled(enabled);
                }
            });
        }
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Edge-to-Edge with proper system insets handling for targetSdk 35/36+
        WindowCompat.setDecorFitsSystemWindows(getWindow(), false);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            getWindow().setStatusBarColor(Color.BLACK);
            getWindow().setNavigationBarColor(Color.BLACK);
        }

        WindowInsetsControllerCompat insetsController = WindowCompat.getInsetsController(getWindow(), getWindow().getDecorView());
        if (insetsController != null) {
            insetsController.setAppearanceLightStatusBars(false); // White icons on black status bar
            insetsController.setAppearanceLightNavigationBars(false); // White buttons on black nav bar
        }

        // Setup Layout with SwipeRefreshLayout and WebView programmatically
        swipeRefreshLayout = new SwipeRefreshLayout(this);
        swipeRefreshLayout.setBackgroundColor(Color.BLACK);
        webView = new WebView(this);
        swipeRefreshLayout.addView(webView);
        setContentView(swipeRefreshLayout);

        // Apply Window Insets so the content is padded inside status bar and navigation bar
        ViewCompat.setOnApplyWindowInsetsListener(swipeRefreshLayout, (v, windowInsets) -> {
            Insets systemBars = windowInsets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return WindowInsetsCompat.CONSUMED;
        });

        // Enable SwipeRefresh by default for general browsing (Dashboard, Villas, Profile, etc.)
        swipeRefreshLayout.setEnabled(true);
        swipeRefreshLayout.setOnRefreshListener(() -> webView.reload());
        swipeRefreshLayout.setColorSchemeResources(R.color.primary, R.color.accent);

        // Prevent pull-to-refresh from activating when scrolling up unless user is at the very top of the page
        swipeRefreshLayout.setOnChildScrollUpCallback((parent, child) -> {
            if (webView != null) {
                return webView.getScrollY() > 0 || webView.canScrollVertically(-1);
            }
            return false;
        });

        // Setup Native File Chooser Launcher (Photo Upload for Villas)
        fileChooserLauncher = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(),
            result -> {
                if (filePathCallback == null) return;
                Uri[] results = null;
                if (result.getResultCode() == RESULT_OK && result.getData() != null) {
                    Intent data = result.getData();
                    if (data.getData() != null) {
                        results = new Uri[]{data.getData()};
                    } else if (data.getClipData() != null) {
                        int count = data.getClipData().getItemCount();
                        results = new Uri[count];
                        for (int i = 0; i < count; i++) {
                            results[i] = data.getClipData().getItemAt(i).getUri();
                        }
                    }
                }
                filePathCallback.onReceiveValue(results);
                filePathCallback = null;
            }
        );

        setupWebViewSettings();
        setupWebViewClients();

        // Hardware Back Button Handler
        getOnBackPressedDispatcher().addCallback(this, new OnBackPressedCallback(true) {
            @Override
            public void handleOnBackPressed() {
                if (webView.canGoBack()) {
                    webView.goBack();
                } else {
                    finish();
                }
            }
        });

        // Load Main App URL
        if (savedInstanceState == null) {
            webView.loadUrl(APP_URL);
        } else {
            webView.restoreState(savedInstanceState);
        }
    }

    private void setupWebViewSettings() {
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setDatabaseEnabled(true);
        settings.setAllowFileAccess(true);
        settings.setAllowContentAccess(true);
        settings.setMediaPlaybackRequiresUserGesture(false);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);
        
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            settings.setMixedContentMode(WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE);
        }

        // Add custom Native Android App User-Agent suffix
        String defaultUA = settings.getUserAgentString();
        settings.setUserAgentString(defaultUA + " TakvimTakipNativeAndroid/1.0");

        // Disable Long-Press Context Menus natively
        webView.setOnLongClickListener(v -> true);
        webView.setHapticFeedbackEnabled(false);

        // Add JavascriptInterface bridge for native controls
        webView.addJavascriptInterface(new WebAppInterface(), "AndroidBridge");
    }

    private void setupWebViewClients() {
        webView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                Uri uri = request.getUrl();
                return handleUri(uri);
            }

            @Override
            public boolean shouldOverrideUrlLoading(WebView view, String url) {
                Uri uri = Uri.parse(url);
                return handleUri(uri);
            }

            private boolean handleUri(Uri uri) {
                if (uri == null) return false;
                String scheme = uri.getScheme();
                String host = uri.getHost();

                // Handle WhatsApp Links (wa.me, whatsapp://)
                if ("whatsapp".equals(scheme) || (host != null && host.contains("wa.me"))) {
                    try {
                        Intent intent = new Intent(Intent.ACTION_VIEW, uri);
                        startActivity(intent);
                        return true;
                    } catch (ActivityNotFoundException e) {
                        Toast.makeText(MainActivity.this, "WhatsApp cihazınızda yüklü değil.", Toast.LENGTH_SHORT).show();
                        return true;
                    }
                }

                // Handle Phone Calls (tel:)
                if ("tel".equals(scheme)) {
                    try {
                        Intent intent = new Intent(Intent.ACTION_DIAL, uri);
                        startActivity(intent);
                        return true;
                    } catch (Exception e) {
                        return false;
                    }
                }

                // Handle Mailto Links (mailto:)
                if ("mailto".equals(scheme)) {
                    try {
                        Intent intent = new Intent(Intent.ACTION_SENDTO, uri);
                        startActivity(intent);
                        return true;
                    } catch (Exception e) {
                        return false;
                    }
                }

                // Internal Application Navigation (takvimtakip.com.tr or localhost)
                if (host != null && (host.contains("takvimtakip.com.tr") || host.contains("localhost"))) {
                    return false; // Load inside WebView
                }

                // External Links -> Open in External Device Browser
                try {
                    Intent intent = new Intent(Intent.ACTION_VIEW, uri);
                    startActivity(intent);
                    return true;
                } catch (Exception e) {
                    return false;
                }
            }

            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
                checkAndToggleSwipeRefresh(url);
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                swipeRefreshLayout.setRefreshing(false);
                checkAndToggleSwipeRefresh(url);
            }

            @Override
            public void doUpdateVisitedHistory(WebView view, String url, boolean isReload) {
                super.doUpdateVisitedHistory(view, url, isReload);
                checkAndToggleSwipeRefresh(url);
            }
        });

        webView.setWebChromeClient(new WebChromeClient() {
            // HTML5 Photo / File Upload Handler
            @Override
            public boolean onShowFileChooser(WebView webView, ValueCallback<Uri[]> filePathCallback, FileChooserParams fileChooserParams) {
                if (MainActivity.this.filePathCallback != null) {
                    MainActivity.this.filePathCallback.onReceiveValue(null);
                }
                MainActivity.this.filePathCallback = filePathCallback;

                Intent intent = fileChooserParams.createIntent();
                try {
                    fileChooserLauncher.launch(intent);
                } catch (ActivityNotFoundException e) {
                    MainActivity.this.filePathCallback = null;
                    Toast.makeText(MainActivity.this, "Dosya seçici açılamadı.", Toast.LENGTH_SHORT).show();
                    return false;
                }
                return true;
            }
        });
    }

    private void checkAndToggleSwipeRefresh(String url) {
        if (swipeRefreshLayout == null) return;
        // Doluluk Takvimi (/occupancy-calendar), Takvim Yönetimi (/calendar), formlar ve detay sayfalarında
        // aşağı çek-yenile özelliği devre dışı bırakılır; diğer genel sayfalarda (Anasayfa, villalar vb.) açık tutulur.
        if (url != null && (url.contains("/occupancy-calendar")
                || url.contains("/calendar")
                || url.contains("/new")
                || url.contains("/edit"))) {
            swipeRefreshLayout.setEnabled(false);
        } else {
            swipeRefreshLayout.setEnabled(true);
        }
    }

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);
        if (webView != null) {
            webView.saveState(outState);
        }
    }
}
