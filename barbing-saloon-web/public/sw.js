const CACHE_NAME_STATIC = 'candycutz-static-v4';
const CACHE_NAME_API = 'candycutz-api-v4';
const CACHE_NAME_MEDIA = 'candycutz-media-v4';
const MAX_MEDIA_ENTRIES = 120;

const VALID_CACHES = [CACHE_NAME_STATIC, CACHE_NAME_API, CACHE_NAME_MEDIA];

async function trimMediaCache(cache) {
  const keys = await cache.keys();
  if (keys.length <= MAX_MEDIA_ENTRIES) return;
  const excess = keys.length - MAX_MEDIA_ENTRIES;
  await Promise.all(keys.slice(0, excess).map((key) => cache.delete(key)));
}

const NETWORK_FIRST_API_PATTERNS = [
  /^\/api\/(?:v1\/)?(?:shop\/location|settings)(?:\/|$)/i,
];

const PUBLIC_API_PATTERNS = [
  /^\/api\/(?:v1\/)?(?:services|barbers|service-categories|categories|gallery|testimonials|blog|working-hours)(?:\/|$)/i,
];

function isCacheableMedia(url) {
  const path = url.pathname.toLowerCase();
  if (
    path.startsWith('/storage/') ||
    path.startsWith('/uploads/') ||
    path.startsWith('/images/') ||
    path.startsWith('/api/v1/private-media/')
  ) {
    return true;
  }

  return /\.(webp|avif|png|jpe?g|gif|svg|ico)$/i.test(path);
}

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(
          keys.map((key) => {
            if (!VALID_CACHES.includes(key)) {
              return caches.delete(key);
            }
          })
        )
      )
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) {
    return;
  }

  const isAuthorized = request.headers.has('Authorization');

  // 1a. Live Shop Location & Site Settings: Network-First with cache fallback so admin updates appear immediately
  if (!isAuthorized && NETWORK_FIRST_API_PATTERNS.some((pattern) => pattern.test(url.pathname))) {
    event.respondWith(
      caches.open(CACHE_NAME_API).then(async (cache) => {
        try {
          const networkResponse = await fetch(request);
          if (networkResponse && networkResponse.status === 200) {
            cache.put(request, networkResponse.clone());
          }
          return networkResponse;
        } catch {
          return (await cache.match(request)) || Response.error();
        }
      })
    );
    return;
  }

  // 1b. Public Catalogue APIs: Stale-While-Revalidate for instant Back/Refresh
  if (!isAuthorized && PUBLIC_API_PATTERNS.some((pattern) => pattern.test(url.pathname))) {
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
          .catch(() => cachedResponse);

        return cachedResponse || fetchPromise;
      })
    );
    return;
  }

  // 2. Uploaded & Static Media (.webp, thumbnails, signed avatars, /storage/): Cache-First
  if (isCacheableMedia(url)) {
    event.respondWith(
      caches.open(CACHE_NAME_MEDIA).then(async (cache) => {
        const cached = await cache.match(request);
        if (cached) {
          return cached;
        }

        try {
          const response = await fetch(request);
          if (response && response.ok && response.status === 200 && response.type !== 'error') {
            await cache.put(request, response.clone());
            trimMediaCache(cache).catch(() => {});
          }
          return response;
        } catch {
          return cached || Response.error();
        }
      })
    );
    return;
  }

  // 3. Static JS/CSS/Fonts: Cache-First (never cache sw.js itself)
  if (
    url.pathname !== '/sw.js' &&
    (url.pathname.startsWith('/assets/') || /\.(js|css|woff2?)$/i.test(url.pathname))
  ) {
    event.respondWith(
      caches.open(CACHE_NAME_STATIC).then(async (cache) => {
        const cached = await cache.match(request);
        if (cached) return cached;

        try {
          const response = await fetch(request);
          if (response && response.ok && response.status === 200 && response.type !== 'error') {
            await cache.put(request, response.clone());
          }
          return response;
        } catch {
          return cached || Response.error();
        }
      })
    );
    return;
  }

  // 4. Page Navigation: Network-first with offline fallback
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(async () => {
        const cache = await caches.open(CACHE_NAME_STATIC);
        return (await cache.match(request)) || cache.match('/index.html');
      })
    );
  }
});
