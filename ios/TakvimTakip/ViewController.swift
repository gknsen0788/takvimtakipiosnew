import UIKit
import WebKit

class ViewController: UIViewController, WKNavigationDelegate, WKUIDelegate, WKScriptMessageHandler {

    private var webView: WKWebView!
    private var refreshControl: UIRefreshControl!
    private let appURLString = "https://www.takvimtakip.com.tr/login"

    override var preferredStatusBarStyle: UIStatusBarStyle {
        return .lightContent
    }

    override func viewDidLoad() {
        super.viewDidLoad()
        overrideUserInterfaceStyle = .light
        view.backgroundColor = UIColor(red: 0x43/255.0, green: 0x38/255.0, blue: 0xca/255.0, alpha: 1.0) // #4338ca header color

        setupWebView()
        setupRefreshControl()
        loadMainURL()
    }

    private func setupWebView() {
        let contentController = WKUserContentController()

        // Inject bridge so web code's `window.AndroidBridge` works seamlessly on iOS too!
        let bridgeScriptSource = """
        if (!window.AndroidBridge) {
            window.AndroidBridge = {
                setSwipeRefreshEnabled: function(enabled) {
                    try {
                        window.webkit.messageHandlers.swipeRefresh.postMessage(Boolean(enabled));
                    } catch(e) {}
                },
                setUserId: function(userId) {
                    try {
                        window.webkit.messageHandlers.setUserId.postMessage(String(userId));
                    } catch(e) {}
                },
                logout: function() {
                    try {
                        window.webkit.messageHandlers.logout.postMessage("");
                    } catch(e) {}
                }
            };
        }
        """
        let bridgeScript = WKUserScript(source: bridgeScriptSource, injectionTime: .atDocumentStart, forMainFrameOnly: false)
        contentController.addUserScript(bridgeScript)
        contentController.add(self, name: "swipeRefresh")
        contentController.add(self, name: "setUserId")
        contentController.add(self, name: "logout")

        let config = WKWebViewConfiguration()
        config.userContentController = contentController
        config.allowsInlineMediaPlayback = true
        config.mediaTypesRequiringUserActionForPlayback = []

        webView = WKWebView(frame: .zero, configuration: config)
        webView.navigationDelegate = self
        webView.uiDelegate = self
        webView.allowsBackForwardNavigationGestures = true
        webView.scrollView.bounces = true
        webView.scrollView.alwaysBounceVertical = false
        webView.backgroundColor = .systemBackground
        webView.isOpaque = false

        // Custom User-Agent matching app conventions
        webView.customUserAgent = "Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 TakvimTakipNativeIOS/1.0"

        webView.translatesAutoresizingMaskIntoConstraints = false
        view.addSubview(webView)

        // Pin to Safe Area or full screen (respecting safe areas)
        NSLayoutConstraint.activate([
            webView.topAnchor.constraint(equalTo: view.safeAreaLayoutGuide.topAnchor),
            webView.leadingAnchor.constraint(equalTo: view.leadingAnchor),
            webView.trailingAnchor.constraint(equalTo: view.trailingAnchor),
            webView.bottomAnchor.constraint(equalTo: view.bottomAnchor)
        ])
    }

    private func setupRefreshControl() {
        refreshControl = UIRefreshControl()
        refreshControl.tintColor = UIColor(red: 0x43/255.0, green: 0x38/255.0, blue: 0xca/255.0, alpha: 1.0)
        refreshControl.addTarget(self, action: #selector(handleRefresh), for: .valueChanged)
        webView.scrollView.refreshControl = refreshControl
    }

    @objc private func handleRefresh() {
        webView.reload()
    }

    private func loadMainURL() {
        if let url = URL(string: appURLString) {
            let request = URLRequest(url: url)
            webView.load(request)
        }
    }

    private func checkAndToggleSwipeRefresh(url: URL?) {
        guard let urlString = url?.absoluteString.lowercased() else { return }

        // Disable pull-to-refresh on calendar/matrix/form screens to avoid accidental reload while dragging
        if urlString.contains("/occupancy-calendar")
            || urlString.contains("/calendar")
            || urlString.contains("/new")
            || urlString.contains("/edit") {
            setSwipeRefreshEnabled(false)
        } else {
            setSwipeRefreshEnabled(true)
        }
    }

    private func setSwipeRefreshEnabled(_ enabled: Bool) {
        DispatchQueue.main.async { [weak self] in
            guard let self = self else { return }
            if enabled {
                if self.webView.scrollView.refreshControl == nil {
                    self.webView.scrollView.refreshControl = self.refreshControl
                }
            } else {
                if self.refreshControl.isRefreshing {
                    self.refreshControl.endRefreshing()
                }
                self.webView.scrollView.refreshControl = nil
            }
        }
    }

    // MARK: - WKScriptMessageHandler
    func userContentController(_ userContentController: WKUserContentController, didReceive message: WKScriptMessage) {
        if message.name == "swipeRefresh", let enabled = message.body as? Bool {
            setSwipeRefreshEnabled(enabled)
        } else if message.name == "setUserId", let userId = message.body as? String {
            NSLog("[OneSignal] Received User ID: %@", userId)
            UserDefaults.standard.set(userId, forKey: "takvim_user_id")
        } else if message.name == "logout" {
            NSLog("[OneSignal] User logged out")
            UserDefaults.standard.removeObject(forKey: "takvim_user_id")
        }
    }

    // MARK: - WKNavigationDelegate
    func webView(_ webView: WKWebView, didStartProvisionalNavigation navigation: WKNavigation!) {
        checkAndToggleSwipeRefresh(url: webView.url)
    }

    func webView(_ webView: WKWebView, didFinish navigation: WKNavigation!) {
        refreshControl.endRefreshing()
        checkAndToggleSwipeRefresh(url: webView.url)
    }

    func webView(_ webView: WKWebView, didFail navigation: WKNavigation!, withError error: Error) {
        refreshControl.endRefreshing()
    }

    func webView(_ webView: WKWebView, didFailProvisionalNavigation navigation: WKNavigation!, withError error: Error) {
        refreshControl.endRefreshing()
    }

    func webView(
        _ webView: WKWebView,
        decidePolicyFor navigationAction: WKNavigationAction,
        decisionHandler: @escaping (WKNavigationActionPolicy) -> Void
    ) {
        guard let url = navigationAction.request.url else {
            decisionHandler(.allow)
            return
        }

        let scheme = url.scheme?.lowercased() ?? ""
        let host = url.host?.lowercased() ?? ""

        // Handle WhatsApp Links (wa.me, whatsapp://)
        if scheme == "whatsapp" || host.contains("wa.me") {
            UIApplication.shared.open(url, options: [:], completionHandler: nil)
            decisionHandler(.cancel)
            return
        }

        // Handle Phone (tel:) & Mail (mailto:)
        if scheme == "tel" || scheme == "mailto" {
            UIApplication.shared.open(url, options: [:], completionHandler: nil)
            decisionHandler(.cancel)
            return
        }

        // Internal app links -> load inside webView
        if host.contains("takvimtakip.com.tr") || host.contains("localhost") {
            decisionHandler(.allow)
            return
        }

        // External links -> open in Safari
        if scheme == "http" || scheme == "https" {
            UIApplication.shared.open(url, options: [:], completionHandler: nil)
            decisionHandler(.cancel)
            return
        }

        decisionHandler(.allow)
    }

    // MARK: - WKUIDelegate (Native Alerts)
    func webView(
        _ webView: WKWebView,
        runJavaScriptAlertPanelWithMessage message: String,
        initiatedByFrame frame: WKFrameInfo,
        completionHandler: @escaping () -> Void
    ) {
        let alert = UIAlertController(title: "TakvimTakip", message: message, preferredStyle: .alert)
        alert.addAction(UIAlertAction(title: "Tamam", style: .default, handler: { _ in completionHandler() }))
        present(alert, animated: true)
    }

    func webView(
        _ webView: WKWebView,
        runJavaScriptConfirmPanelWithMessage message: String,
        initiatedByFrame frame: WKFrameInfo,
        completionHandler: @escaping (Bool) -> Void
    ) {
        let alert = UIAlertController(title: "TakvimTakip", message: message, preferredStyle: .alert)
        alert.addAction(UIAlertAction(title: "İptal", style: .cancel, handler: { _ in completionHandler(false) }))
        alert.addAction(UIAlertAction(title: "Tamam", style: .default, handler: { _ in completionHandler(true) }))
        present(alert, animated: true)
    }
}
