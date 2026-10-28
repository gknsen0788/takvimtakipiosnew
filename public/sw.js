// TakvimTakip Service Worker for PWA, Android App & Web Push Notifications
const CACHE_NAME = 'takvimtakip-pwa-v8';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Dynamic Network-first Strategy for Live PHP Application
    if (event.request.method !== 'GET') return;
    
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});

// -------------------------------------------------------------
// WEB PUSH NOTIFICATION RECEIVE EVENT (RFC 8291 / RFC 8292)
// -------------------------------------------------------------
self.addEventListener('push', (event) => {
    let payload = {
        title: 'TakvimTakip Bildirimi',
        body: 'Yeni bir bildiriminiz var.',
        url: '/dashboard',
        icon: '/css/icon-192.png?v=8',
        badge: '/css/icon-192.png?v=8'
    };

    if (event.data) {
        try {
            const parsed = event.data.json();
            payload = Object.assign(payload, parsed);
        } catch (e) {
            payload.body = event.data.text();
        }
    }

    const options = {
        body: payload.body,
        icon: payload.icon || '/css/icon-192.png?v=8',
        badge: payload.badge || '/css/icon-192.png?v=8',
        data: {
            url: payload.url || '/dashboard'
        },
        vibrate: [100, 50, 100],
        renotify: true,
        tag: 'takvimtakip-' + Date.now()
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, options)
    );
});

// -------------------------------------------------------------
// NOTIFICATION CLICK EVENT (OPEN / FOCUS APP)
// -------------------------------------------------------------
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = (event.notification.data && event.notification.data.url) 
        ? event.notification.data.url 
        : '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if ('focus' in client) {
                    if (client.url.includes(targetUrl) || client.url.includes('/dashboard')) {
                        return client.focus();
                    }
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
