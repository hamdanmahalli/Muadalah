const CACHE_NAME = 'smart-pesantren-v2';
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
    // Aktifkan SW baru tanpa menunggu tab lain ditutup
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
    // Ambil kontrol dari SW lama segera
    self.clients.claim();
});

// 3. Network-first: ambil dari internet dulu, fallback ke cache jika offline
self.addEventListener('fetch', event => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Simpan ke cache jika response valid
                if (response && response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // Jika offline, coba ambil dari cache
                return caches.match(event.request);
            })
    );
});
