// Service Worker - Aggressive Caching Strategy
const CACHE_VERSION = 'qiyamm-v1.1';
const CACHE_STATIC = `${CACHE_VERSION}-static`;
const CACHE_DYNAMIC = `${CACHE_VERSION}-dynamic`;
const CACHE_IMAGES = `${CACHE_VERSION}-images`;

// Assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/css/landing.min.css',
    '/js/landing.min.js',
    '/js/theme.min.js',
    '/icons.svg',
    '/FONT/IBMPlexSansArabic-Regular.ttf',
    '/FONT/IBMPlexSansArabic-Bold.ttf'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(CACHE_STATIC)
            .then(cache => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - cleanup old caches
self.addEventListener('activate', event => {
    console.log('[SW] Activating...');
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys
                    .filter(key => key !== CACHE_STATIC && key !== CACHE_DYNAMIC && key !== CACHE_IMAGES)
                    .map(key => {
                        console.log('[SW] Deleting old cache:', key);
                        return caches.delete(key);
                    })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - cache first strategy
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip external requests
    if (!url.origin.includes(self.location.origin)) return;

    // Images - Cache first, network fallback
    if (request.destination === 'image') {
        event.respondWith(
            caches.open(CACHE_IMAGES).then(cache => {
                return cache.match(request).then(response => {
                    return response || fetch(request).then(networkResponse => {
                        cache.put(request, networkResponse.clone());
                        return networkResponse;
                    });
                });
            })
        );
        return;
    }

    // Static assets - Cache first
    if (url.pathname.match(/\.(css|js|woff2?|ttf|svg)$/)) {
        event.respondWith(
            caches.match(request).then(response => {
                return response || fetch(request).then(networkResponse => {
                    return caches.open(CACHE_STATIC).then(cache => {
                        cache.put(request, networkResponse.clone());
                        return networkResponse;
                    });
                });
            })
        );
        return;
    }

    // HTML - Network first, cache fallback
    if (request.headers.get('accept').includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    const responseClone = response.clone();
                    caches.open(CACHE_DYNAMIC).then(cache => {
                        cache.put(request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    return caches.match(request);
                })
        );
        return;
    }

    // Default - Network first
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});

// Background sync for offline actions
self.addEventListener('sync', event => {
    console.log('[SW] Background sync:', event.tag);
    if (event.tag === 'sync-data') {
        event.waitUntil(syncData());
    }
});

async function syncData() {
    // Implement your sync logic here
    console.log('[SW] Syncing data...');
}
