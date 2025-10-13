const CACHE_NAME = 'tienda-dux-v5';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/images/icons/icon-192x192.png',
  '/images/icons/icon-512x512.png',
];

self.addEventListener('install', function(event) {
  console.log('Service Worker instalando...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Cache abierto: ' + CACHE_NAME);
        return cache.addAll(urlsToCache)
          .catch(err => {
            console.log('Error al cachear algunos archivos:', err);
            return Promise.resolve();
          });
      })
  );
  
  self.skipWaiting();
});

// Interceptar las requests - ESTRATEGIA MEJORADA
self.addEventListener('fetch', function(event) {
  const url = new URL(event.request.url);
  
  // No cachear POST
  if (event.request.method !== 'GET') {
    event.respondWith(fetch(event.request));
    return;
  }
  
  // No cachear rutas dinámicas (HTML)
  if (url.pathname.includes('/api/') || 
      url.pathname.includes('/livewire/') ||
      url.pathname === '/checkout' ||
      url.pathname === '/' ||
      url.pathname.endsWith('.html')) {
    event.respondWith(fetch(event.request));
    return;
  }
  
  // Solo cachear assets estáticos (CSS, JS, imágenes, fonts)
  if (url.pathname.match(/\.(js|css|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot)$/i)) {
    event.respondWith(
      caches.match(event.request)
        .then(function(response) {
          if (response) {
            return response;
          }
          
          return fetch(event.request)
            .then(function(response) {
              if (response && response.status === 200) {
                const responseToCache = response.clone();
                caches.open(CACHE_NAME)
                  .then(function(cache) {
                    cache.put(event.request, responseToCache);
                  });
              }
              return response;
            });
        })
    );
  } else {
    // Todo lo demás, fetch del servidor sin cachear
    event.respondWith(fetch(event.request));
  }
});

self.addEventListener('activate', function(event) {
  console.log('Service Worker activando...');
  
  event.waitUntil(
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          if (cacheName !== CACHE_NAME) {
            console.log('Borrando caché antiguo: ' + cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  
  self.clients.claim();
});

// Push notifications (igual que antes)
self.addEventListener('push', function(event) {
  const options = {
    body: event.data ? event.data.text() : 'Nueva notificación de Tienda DuX',
    icon: '/images/icons/icon-192x192.png',
    badge: '/images/icons/icon-72x72.png',
    tag: 'tienda-dux-notification',
    requireInteraction: true,
    actions: [
      {
        action: 'view',
        title: 'Ver',
        icon: '/images/icons/icon-72x72.png'
      },
      {
        action: 'close',
        title: 'Cerrar',
        icon: '/images/icons/icon-72x72.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('Tienda DuX', options)
  );
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  
  if (event.action === 'view') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});