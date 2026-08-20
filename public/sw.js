const STATIC_CACHE = 'terrain-static-v2';
const APP_SHELL = [
    '/',
    '/offline.html',
    '/manifest.json',
    '/icons/icon-192.svg',
    '/icons/icon-512.svg',
    '/icons/maskable-512.svg',
    '/styles/app.css',
];

function fetchAndCache(request) {
    return fetch(request).then((networkResponse) => {
        if (networkResponse.ok) {
            const clone = networkResponse.clone();
            caches.open(STATIC_CACHE).then((cache) => cache.put(request, clone));
        }
        return networkResponse;
    });
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(APP_SHELL))
    );

    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== STATIC_CACHE)
                    .map((key) => caches.delete(key))
            )
        )
    );

    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() =>
                caches.match(request).then((cached) => cached || caches.match('/offline.html'))
            )
        );
        return;
    }

    const url = new URL(request.url);
    const isStaticAsset = ['style', 'script', 'image', 'font'].includes(request.destination);

    if (url.origin !== self.location.origin && !isStaticAsset) {
        return;
    }

    // Ressources statiques : cache d'abord, elles ne bougent pas
    if (isStaticAsset) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetchAndCache(request))
        );
        return;
    }

    event.respondWith(
        fetchAndCache(request).catch(() => caches.match(request))
    );
});