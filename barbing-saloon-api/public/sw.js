const CACHE_NAME_STATIC = 'candycutz-static-v1';
const CACHE_NAME_API = 'candycutz-api-v1';

const PUBLIC_API_PATTERNS = [
  /\/api\/(v1\/)?(services|barbers|categories|gallery|testimonials|blog|settings|working-hours)/i,
];

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME_STATIC && key !== CACHE_NAME_API) {
            return caches.delete(key);
          }
        })
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  // Only handle GET requests
  if (request.method !== 'GET') {
    return;
  }

  // 1. Public Catalogue & Marketing API Requests: Stale-While-Revalidate
  const isPublicApi = PUBLIC_API_PATTERNS.some((pattern) => pattern.test(url.pathname));
  if (isPublicApi) {
    event.respondWith(
      caches.open(CACHE_NAME_API).then(async (cache) => {
        const cachedResponse = await cache.match(request);

        const fetchPromise = fetch(request)
          .then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              cache.put(request, networkResponse.clone());
            }
            return networkResponse;
          })
          .catch(() => {
            // Return cached response with stale header if network failed
            if (cachedResponse) {
              const headers = new Headers(cachedResponse.headers);
              headers.set('X-Cache-Stale', '1');
              return new Response(cachedResponse.body, {
                status: cachedResponse.status,
                statusText: cachedResponse.statusText,
                headers,
              });
            }
          });

        return cachedResponse || fetchPromise;
      })
    );
    return;
  }

  // 2. Navigation / Page Requests: Network first, cache fallback
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(async () => {
        const cache = await caches.open(CACHE_NAME_STATIC);
        const cached = await cache.match(request);
        return cached || cache.match('/index.html');
      })
    );
    return;
  }

  // 3. Static assets: Cache-first with network fallback
  if (
    url.pathname.startsWith('/assets/') ||
    url.pathname.endsWith('.js') ||
    url.pathname.endsWith('.css') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.jpg') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.woff2')
  ) {
    event.respondWith(
      caches.open(CACHE_NAME_STATIC).then(async (cache) => {
        const cached = await cache.match(request);
        if (cached) return cached;

        try {
          const response = await fetch(request);
          if (response && response.status === 200) {
            cache.put(request, response.clone());
          }
          return response;
        } catch (e) {
          return cached;
        }
      })
    );
  }
});