/**
 * Service Worker para Cache de Posts
 * Melhora performance em visitas repetidas
 */

const CACHE_NAME = 'cchla-v1';
const urlsToCache = [
    '/',
    '/wp-content/themes/cchla-ufrn/assets/css/single-post.css',
    '/wp-content/themes/cchla-ufrn/assets/img/logo.svg',
];

// Instala o Service Worker
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

// Intercepta requisições
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => response || fetch(event.request))
    );
});