<!-- PWA Manifest -->
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#38bdf8">
<link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

<!-- Firebase SDK (Modular) -->
<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
  import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js";

  // Configuración de Firebase desde las variables de entorno de Laravel
  const firebaseConfig = {
    apiKey: "{{ env('VITE_FIREBASE_API_KEY') }}",
    authDomain: "{{ env('VITE_FIREBASE_AUTH_DOMAIN') }}",
    projectId: "{{ env('VITE_FIREBASE_PROJECT_ID') }}",
    storageBucket: "{{ env('VITE_FIREBASE_STORAGE_BUCKET') }}",
    messagingSenderId: "{{ env('VITE_FIREBASE_MESSAGING_SENDER_ID') }}",
    appId: "{{ env('VITE_FIREBASE_APP_ID') }}"
  };

  // Solo inicializamos si hay configuración válida
  if (firebaseConfig.apiKey && firebaseConfig.apiKey !== '') {
    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    // Solicitar permiso para notificaciones
    function requestNotificationPermission() {
        console.log('Requesting notification permission...');
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                console.log('Notification permission granted.');
                // Obtener el token de FCM
                getToken(messaging, { vapidKey: 'YOUR_PUBLIC_VAPID_KEY_HERE' })
                    .then((currentToken) => {
                        if (currentToken) {
                            console.log('FCM Token:', currentToken);
                            // TODO: Enviar el token al servidor (Laravel) para guardarlo con el usuario
                        } else {
                            console.log('No registration token available.');
                        }
                    }).catch((err) => {
                        console.log('An error occurred while retrieving token. ', err);
                    });
            } else {
                console.log('Unable to get permission to notify.');
            }
        });
    }

    // Manejar mensajes en primer plano
    onMessage(messaging, (payload) => {
        console.log('Message received. ', payload);
        // Opcional: mostrar una notificación toast en la UI usando Bootstrap/Toastify
    });

    // Exponer la función globalmente si queremos pedir permisos con un botón
    window.requestFirebaseNotificationPermission = requestNotificationPermission;
  }
</script>

<!-- Service Worker Registration for PWA -->
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/firebase-messaging-sw.js').then(function(registration) {
        console.log('ServiceWorker registration successful with scope: ', registration.scope);
      }, function(err) {
        console.log('ServiceWorker registration failed: ', err);
      });
    });
  }
</script>
