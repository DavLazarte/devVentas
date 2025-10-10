const CACHE_NAME = 'tienda-dux-v4'; // IMPORTANTE: Cambia esto cada vez que deploys
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/images/icons/icon-192x192.png',
  '/images/icons/icon-512x512.png',
];

// Instalación del Service Worker
self.addEventListener('install', function(event) {
  console.log('Service Worker instalando...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Cache abierto: ' + CACHE_NAME);
        return cache.addAll(urlsToCache)
          .catch(err => {
            console.log('Error al cachear algunos archivos:', err);
            // No fallar si alguno no está disponible
            return Promise.resolve();
          });
      })
  );
  
  // IMPORTANTE: Fuerza la activación inmediata
  self.skipWaiting();
});

// Interceptar las requests
self.addEventListener('fetch', function(event) {
  // No cachear solicitudes POST y ciertas rutas
  if (event.request.method !== 'GET' || 
      event.request.url.includes('/api/') || 
      event.request.url.includes('/livewire/')) {
    event.respondWith(fetch(event.request));
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        // Cache hit - devolver respuesta del cache
        if (response) {
          return response;
        }
        
        return fetch(event.request)
          .then(function(response) {
            // Si es un GET exitoso, guardar en caché
            if (response && response.status === 200 && event.request.method === 'GET') {
              const responseToCache = response.clone();
              caches.open(CACHE_NAME)
                .then(function(cache) {
                  cache.put(event.request, responseToCache);
                });
            }
            return response;
          })
          .catch(function() {
            // Fallback offline
            return new Response('Offline - recurso no disponible', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            });
          });
      }
    )
  );
});

// Actualización del Service Worker
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
  
  // Tomar control de todas las páginas inmediatamente
  self.clients.claim();
});

// Push notifications
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

// Manejar clicks en notificaciones
self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  
  if (event.action === 'view') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});