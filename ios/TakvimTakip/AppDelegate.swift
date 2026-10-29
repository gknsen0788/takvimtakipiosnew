import UIKit
import OneSignalFramework

@main
class AppDelegate: UIResponder, UIApplicationDelegate {

    var window: UIWindow?
    private static let oneSignalAppId = "7c2b7d4b-9b10-4207-85df-a911a52ef70c"

    func application(
        _ application: UIApplication,
        didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
    ) -> Bool {
        // OneSignal 5.x Initialization
        OneSignal.initialize(AppDelegate.oneSignalAppId, withLaunchOptions: launchOptions)

        // If a user ID was previously persisted, log in immediately
        if let savedUserId = UserDefaults.standard.string(forKey: "takvim_user_id"), !savedUserId.isEmpty {
            OneSignal.login(savedUserId)
        }

        // Request notification permission from the user
        OneSignal.Notifications.requestPermission({ accepted in
            print("[OneSignal] Notification permission status: \(accepted)")
            if let savedUserId = UserDefaults.standard.string(forKey: "takvim_user_id"), !savedUserId.isEmpty {
                OneSignal.login(savedUserId)
            }
        }, fallbackToSettings: true)

        return true
    }

    // MARK: UISceneSession Lifecycle

    func application(
        _ application: UIApplication,
        configurationForConnecting connectingSceneSession: UISceneSession,
        options: UIScene.ConnectionOptions
    ) -> UISceneConfiguration {
        return UISceneConfiguration(name: "Default Configuration", sessionRole: connectingSceneSession.role)
    }

    func application(
        _ application: UIApplication,
        didDiscardSceneSessions sceneSessions: Set<UISceneSession>
    ) {
    }
}
