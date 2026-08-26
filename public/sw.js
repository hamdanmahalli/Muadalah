const CACHE_NAME = 'smart-pesantren-v1';
const urlsToCache = [
    '/',
    '/manifest.json'
];

// 1. Saat asisten (Service Worker) pertama kali dipasang di HP pengguna
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Asisten berhasil menyimpan data awal');
                return cache.addAll(urlsToCache);
            })
    );
});

// 2. Saat aplikasi sedang digunakan, asisten akan membantu memuat halaman
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Jika data sudah diingat asisten, berikan langsung. Jika belum, ambil dari internet.
                return response || fetch(event.request);
            })
    );
});