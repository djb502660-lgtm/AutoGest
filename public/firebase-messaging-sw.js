// Import and configure the Firebase SDK
// These scripts are made available when the app is served or cached on 127.0.0.1 and when the service worker is active.
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

// Configuración de Firebase (Se inicializa desde el cliente, pero el SW requiere su propia copia si manejará background)
// Como las variables ENV no están aquí, usaremos la URL parameter u otra técnica, o dejamos un placeholder
// Para Push Background Notifications, se debe colocar la misma config.
// NOTA: Reemplazar con las credenciales reales de Firebase.
const firebaseConfig = {
    apiKey: "AIzaSyCUJr6RDUhWwTc4ygrMJoYgNRB_RugwJc8",
    authDomain: "autogest-53d83.firebaseapp.com",
    projectId: "autogest-53d83",
    storageBucket: "autogest-53d83.firebasestorage.app",
    messagingSenderId: "458603075352",
    appId: "1:458603075352:web:95d519fda9a0f551e8f06d"
};

// Evita errores si se inicializa más de una vez
if (!firebase.apps.length && firebaseConfig.apiKey !== "REPLACE_WITH_API_KEY") {
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();
    
    // Background message handler
    messaging.onBackgroundMessage(function(payload) {
      console.log('[firebase-messaging-sw.js] Received background message ', payload);
      
      const notificationTitle = payload.notification?.title || 'Notificación de AutoGest';
      const notificationOptions = {
        body: payload.notification?.body,
        icon: '/favicon.svg'
      };
    
      self.registration.showNotification(notificationTitle, notificationOptions);
    });
}

// Basic PWA Service Worker caching for offline fallback (optional)
const CACHE_NAME = 'autogest-v1';
const urlsToCache = [
  '/',
  '/favicon.svg',
  '/manifest.json'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  // Simple network-first approach or fallback strategy can be implemented here
  // For now we just let the network handle it, unless offline
});
