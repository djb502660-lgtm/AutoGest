# Build APK 1.3.0 locally (sin cuota EAS).
# Requisitos: Android Studio + SDK, JAVA_HOME apuntando al JBR de Studio.
#
# Uso:
#   .\scripts\build-apk-local.ps1
#
# Si Gradle falla con "Could not move temporary workspace", cierra Cursor/AV,
# abre Android Studio → Open → AutoGestExpo/android → Build → Build APK(s).

param(
    [ValidateSet('release', 'debug')]
    [string]$Variant = 'release'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$android = Join-Path $root 'android'

if (-not (Test-Path $android)) {
    Write-Host 'Generando proyecto nativo (expo prebuild)...' -ForegroundColor Cyan
    Push-Location $root
    npx expo prebuild --platform android --no-install
    Pop-Location
}

if (-not $env:JAVA_HOME) {
    $studioJbr = 'C:\Program Files\Android\Android Studio\jbr'
    if (Test-Path $studioJbr) {
        $env:JAVA_HOME = $studioJbr
    }
}

if (-not $env:ANDROID_HOME) {
    $sdk = Join-Path $env:LOCALAPPDATA 'Android\Sdk'
    if (Test-Path $sdk) {
        $env:ANDROID_HOME = $sdk
    }
}

# Evita rutas >260 chars en Windows (Gradle cache en sandbox o temp largo).
if (-not $env:GRADLE_USER_HOME) {
    $shortGradle = 'C:\gradle'
    New-Item -ItemType Directory -Force -Path $shortGradle | Out-Null
    $env:GRADLE_USER_HOME = $shortGradle
}

# Restaura propiedades Expo si gradle.properties fue recortado.
$gradleProps = Join-Path $android 'gradle.properties'
$requiredProps = @(
    'hermesEnabled=true',
    'newArchEnabled=false',
    'android.useAndroidX=true'
)
if (Test-Path $gradleProps) {
    $content = Get-Content $gradleProps -Raw
    foreach ($prop in $requiredProps) {
        $key = ($prop -split '=')[0]
        if ($content -notmatch "(?m)^$key=") {
            Add-Content -Path $gradleProps -Value $prop
        }
    }
}

if (-not $env:JAVA_HOME) {
    Write-Error 'JAVA_HOME no configurado. Instala Android Studio o define JAVA_HOME.'
}

$task = if ($Variant -eq 'debug') { 'assembleDebug' } else { 'assembleRelease' }
Write-Host "JAVA_HOME=$env:JAVA_HOME" -ForegroundColor DarkGray
Write-Host "ANDROID_HOME=$env:ANDROID_HOME" -ForegroundColor DarkGray
Write-Host "Ejecutando gradlew $task ..." -ForegroundColor Cyan

Push-Location $android
try {
    & .\gradlew.bat $task --no-daemon
    if ($LASTEXITCODE -ne 0) {
        throw "Gradle terminó con código $LASTEXITCODE"
    }
}
finally {
    Pop-Location
}

$apkDir = if ($Variant -eq 'debug') {
    Join-Path $android 'app\build\outputs\apk\debug'
} else {
    Join-Path $android 'app\build\outputs\apk\release'
}

$apk = Get-ChildItem -Path $apkDir -Filter '*.apk' -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending | Select-Object -First 1
if ($apk) {
    $dest = Join-Path $root "AutoGest-$Variant-1.3.0.apk"
    Copy-Item $apk.FullName $dest -Force
    $laravelPublic = Join-Path (Split-Path -Parent $root) 'public\downloads'
    New-Item -ItemType Directory -Force -Path $laravelPublic | Out-Null
    $publicApk = Join-Path $laravelPublic 'AutoGest-1.3.0.apk'
    Copy-Item $apk.FullName $publicApk -Force
    Write-Host "`nAPK listo:" -ForegroundColor Green
    Write-Host $dest
    Write-Host $publicApk
    Write-Host "`nActualiza ANDROID_APK_URL en Laravel, p. ej.:"
    Write-Host "  http://autogest.test/downloads/AutoGest-1.3.0.apk"
    Write-Host "  https://autogest-jlm7.onrender.com/downloads/AutoGest-1.3.0.apk"
}
else {
    Write-Warning "No se encontró APK en $apkDir"
}
