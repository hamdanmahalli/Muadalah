const CACHE_NAME = 'smart-pesantren-v4';
const PRECACHE_URLS = [
    '/manifest.json',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/dashboard-guru',
    '/kaldik',
    '/rekap-presensi',
    '/menu'
];

const isStaticAsset = url => {
    const pathname = url.pathname;
    return pathname.startsWith('/icons/') || pathname === '/manifest.json';
};

// 1. Saat Service Worker pertama kali dipasang: simpan halaman inti agar bisa dibuka offline
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

// 2. Aktifkan SW baru & hapus cache versi lama
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Strategi caching:
//    - Aset statis (icon/manifest): cache-first (cepat, tidak pernah berubah)
//    - Halaman HTML: network-first (fresh saat online, cache saat offline)
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    if (url.origin !== location.origin) return;

    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                return cached || fetch(event.request).then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                });
            })
        );
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(response => {
                if (response && response.status === 200) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request))
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