const CACHE_NAME = 'smart-pesantren-v3';
const urlsToCache = [
    '/manifest.json'
];

// 1. Saat Service Worker pertama kali dipasang
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('SW: Cache aktif');
                return cache.addAll(urlsToCache);
            })
    );
    self.skipWaiting();
});

// 2. Hapus cache lama saat update
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

// 3. Network-first
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;
    event.respondWith(
        fetch(event.request)
            .then(response => {
                if (response && response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                return caches.match(event.request);
            })
    );
});

// 4. Push Notification
self.addEventListener('push', function(event) {
    let data = { title: 'Smart Pesantren', body: 'Ada notifikasi baru', tag: 'default', url: '/dashboard-guru', mode: 'sound' };

    if (event.data) {
        try {
            data = event.data.json();
        } catch(e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon || '/icons/icon-192x192.png',
        badge: data.badge || '/icons/icon-192x192.png',
        tag: data.tag || 'smart-pesantren',
        vibrate: data.mode === 'silent' ? [] : [200, 100, 200],
        silent: data.mode === 'silent',
        requireInteraction: false,
        data: { url: data.url || '/dashboard-guru' },
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// 5. Notification Click
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const url = event.notification.data?.url || '/dashboard-guru';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            for (let client of windowClients) {
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }
            return clients.openWindow(url);
        })
    );
});
