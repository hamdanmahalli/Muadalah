const CACHE_NAME = 'smart-pesantren-v10';
const PRECACHE_URLS = [
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/offline.html',
    '/dashboard-guru',
    '/kaldik',
    '/rekap-presensi',
    '/scan-kelas',
    '/menu'
];

const isStaticAsset = url => {
    const pathname = url.pathname;
    return pathname.startsWith('/icons/');
};

// 1. Saat Service Worker pertama kali dipasang: simpan halaman inti agar bisa dibuka offline
//    Menggunakan add() per URL (bukan addAll) agar satu URL yang gagal (mis. karena redirect login)
//    tidak membatalkan seluruh proses instalasi cache.
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return Promise.allSettled(
                    PRECACHE_URLS.map(url => cache.add(url))
                );
            })
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
//    - Ketika offline & tidak ada cache: sajikan /offline.html agar tidak layar hitam
const serveOfflineFallback = event => {
    // Sajikan file fallback untuk permintaan navigasi halaman
    if (event.request.mode === 'navigate' || event.request.destination === 'document') {
        return caches.match('/offline.html');
    }
    return Response.error();
};

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    if (url.origin !== location.origin) return;

    // Navigasi halaman (HTML): SELALU ambil fresh dari server saat online.
    // paksa cache:no-store agar browser tidak menyajikan halaman lama dari HTTP cache,
    // sehingga tidak perlu refresh manual tiap kali membuka halaman.
    if (event.request.mode === 'navigate' || event.request.destination === 'document') {
        const freshRequest = new Request(event.request, { cache: 'no-store' });
        event.respondWith(
            fetch(freshRequest)
                .then(response => {
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    }
                    return response;
                })
                .catch(() =>
                    caches.match(event.request).then(cached => {
                        if (cached) return cached;
                        return caches.match('/offline.html');
                    })
                )
        );
        return;
    }

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
            .catch(() =>
                caches.match(event.request).then(cached => {
                    if (cached) return cached;
                    return serveOfflineFallback(event);
                })
            )
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

    const mode = data.mode === 'vibrate' ? 'vibrate' : (data.mode === 'silent' ? 'silent' : 'sound');

    const options = {
        body: data.body,
        icon: data.icon || '/icons/icon-192x192.png',
        badge: data.badge || '/icons/icon-192x192.png',
        tag: data.tag || 'smart-pesantren',
        renotify: true,
        vibrate: mode === 'silent' ? [] : [200, 100, 200],
        silent: mode === 'silent',
        data: { url: data.url || '/dashboard-guru' },
        actions: [
            { action: 'open', title: 'Buka Aplikasi' }
        ],
    };

    event.waitUntil(
        Promise.all([
            self.registration.showNotification(data.title, options),
            fetch('/notifikasi/pulse', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tag: data.tag || 'unknown', title: data.title || '' }),
            }).catch(function() {})
        ]).catch(function(err) {
            console.log('Push gagal diproses:', err);
        })
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