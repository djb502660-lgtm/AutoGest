# ADR-004: Estrategia WebView para Integración Android

## Estado
Propuesta

## Contexto y Problema
AutoGest requerirá una aplicación Android que consumirá la funcionalidad web existente. Las opciones principales son:
1. Aplicación nativa completa (costo alto, tiempo largo)
2. Aplicación híbrida (WebView con componentes nativos)
3. PWA (Progressive Web App)

Problemas a resolver:
- Maximizar reutilización de código web existente
- Experiencia nativa optimizada para móvil
- Mantenimiento simplificado (una sola base de código)
- Acceso a funcionalidades nativas (cámara, GPS, notificaciones)
- Performance aceptable

## Decisiones Consideradas

### Opción 1: Aplicación nativa completa
- **Ventajas:**
  - Mejor performance y UX nativa
  - Acceso completo a APIs nativas
  - Experiencia optimizada para Android
- **Desventajas:**
  - Costo de desarrollo muy alto
  - Tiempo de desarrollo prolongado
  - Duplicación de lógica de negocio
  - Mantenimiento complejo (dos bases de código)
  - No reutiliza inversión web existente

### Opción 2: PWA (Progressive Web App)
- **Ventajas:**
  - Una sola base de código
  - Instalable desde navegador
  - Actualizaciones automáticas
  - Bajo costo de desarrollo
- **Desventajas:**
  - Acceso limitado a APIs nativas
  - Performance inferior a nativa
  - Experiencia de usuario menos nativa
  - Dependencia de navegador
  - Limitaciones en algunas funcionalidades

### Opción 3: WebView híbrido con componentes nativos
- **Ventajas:**
  - Reutilización máxima de código web existente
  - Experiencia nativa donde es necesario
  - Acceso a APIs nativas (cámara, GPS, notificaciones)
  - Costo de desarrollo medio
  - Mantenimiento simplificado (base web principal)
  - Tiempo de desarrollo razonable
- **Desventajas:**
  - Performance inferior a nativo puro
  - Requiere puente JavaScript-Nativo
  - Algunas limitaciones de WebView
  - Necesita optimización para móvil

## Decisión
Implementar estrategia WebView híbrido con componentes nativos, enfocándose en:

### Arquitectura Híbrida
```
Android App (Contenedor Nativo)
├── WebView (Carga interfaz web AutoGest)
├── Puentes Nativos (Bridges)
│   ├── CameraBridge (acceso a cámara/galería)
│   ├── GPSBridge (geolocalización)
│   ├── NotificationBridge (notificaciones push)
│   ├── StorageBridge (almacenamiento local)
│   └── BiometricBridge (autenticación biométrica)
├── Componentes Nativos
│   ├── Splash Screen
│   ├── Navigation Bar personalizado
│   ├── Offline Manager
│   └── Error Handler
└── Servicios Nativos
    ├── Background Sync
    ├── Push Notifications
    └── Deep Links
```

### Responsabilidades de Cada Capa
**Web (AutoGest existente):**
- UI principal de la aplicación
- Lógica de negocio
- Gestión de estado
- Navegación entre vistas
- Formularios y datos

**Android (Contenedor):**
- Acceso a hardware nativo
- Funcionalidades offline
- Notificaciones push
- Deep linking
- Performance de renderizado
- Gestión de caché

### Estrategia de Comunicación
```javascript
// Desde WebView a Android
window.AndroidBridge.takePhoto((photoData) => {
    // Procesar foto
});

// Desde Android a WebView
webView.evaluateJavascript("updateLocation(" + lat + "," + lng + ")");
```

## Consecuencias

### Positivas
- Reutilización del 80-90% del código web existente
- Costo de desarrollo significativamente menor que nativo
- Mantenimiento simplificado (una base principal)
- Acceso a funcionalidades nativas críticas
- Tiempo de desarrollo razonable
- Experiencia cercana a nativa

### Negativas
- Performance inferior a nativo puro
- Complejidad de puente JavaScript-Nativo
- Dependencia de rendimiento de WebView
- Algunas limitaciones de funcionalidades
- Requiere optimización web para móvil

### Riesgos
- Performance de WebView en dispositivos antiguos
- Complejidad de debugging híbrido
- Compatibilidad entre versiones de Android
- Limitaciones de memoria en WebView
- Problemas de seguridad en puente JS-Nativo

## Implementación

### Criterios Mobile-First para Desarrollo Web
Desde ahora, todas las nuevas funcionalidades deben cumplir:

#### Responsive Design
- Breakpoints: móvil (<768px), tablet (768-1024px), desktop (>1024px)
- Grid fluido y flexible
- Imágenes responsive con srcset
- Touch targets mínimos de 44x44px

#### Optimización Móvil
- Touch-friendly interactions (no hover)
- Swipe gestures donde apropiado
- Input types correctos (tel, email, number)
- Autocomplete/correction configurado
- Keyboard types apropiados

#### Performance Móvil
- Lazy loading de imágenes
- Code splitting por ruta
- Minificación de CSS/JS
- Optimización de fonts
- Reducción de requests HTTP

#### Android-Specific
- Viewport meta tag optimizado
- Safe area insets para notches
- Back button handling
- Orientation lock donde apropiado
- Fullscreen mode donde apropiado

### Archivos afectados (Fase Desarrollo Android)
- `android/` (nuevo proyecto Android)
- `resources/views/` (optimizar para móvil)
- `public/build/` (assets optimizados)
- `app/Http/Controllers/` (endpoints para bridges)
- `routes/api.php` (endpoints para comunicación)

### Esfuerzo estimado
- FASE final del proyecto
- 4-6 semanas de desarrollo Android
- 2 semanas de integración WebView
- 1 semana de testing y optimización
- 1 semana de deployment

### Dependencias
- Completar todas las fases web
- Interfaz web optimizada mobile-first
- API endpoints estables
- Tests de integración Android-Web

### Modelo de implementación Android
```kotlin
class MainActivity : AppCompatActivity() {
    private lateinit var webView: WebView
    private lateinit var androidBridge: AndroidBridge
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)
        
        webView = findViewById(R.id.webView)
        androidBridge = AndroidBridge(this)
        
        configureWebView()
        setupBridges()
        loadApp()
    }
    
    private fun configureWebView() {
        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            cacheMode = WebSettings.LOAD_DEFAULT
        }
        
        webView.addJavascriptInterface(androidBridge, "AndroidBridge")
    }
    
    private fun setupBridges() {
        androidBridge.setCameraListener { photoData ->
            // Manejar foto desde cámara
        }
    }
}

class AndroidBridge(private val context: Context) {
    @JavascriptInterface
    fun takePhoto(callbackId: String) {
        // Abrir cámara y retornar resultado
    }
    
    @JavascriptInterface
    fun getLocation(callbackId: String) {
        // Obtener GPS
    }
}
```

## Referencias
- [Android WebView Best Practices](https://developer.android.com/guide/webapps/webview)
- [Progressive Web Apps vs Hybrid Apps](https://www.smashingmagazine.com/2020/10/pwas-vs-hybrid-apps/)
- [Mobile-First Design](https://www.smashingmagazine.com/2020/10/mobile-first-design/)
- Baseline actual: docs/BASELINE/dependency-matrix.md

## Fecha
2026-08-04

## Autor
Technical Lead - AutoGest Project
