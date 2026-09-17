<#
.SYNOPSIS
    CandyCutz — Local Android APK Build Script
.DESCRIPTION
    Sets up environment variables, runs expo prebuild, builds the debug APK
    via Gradle, and installs it on a connected Android device.
#>

param(
    [switch]$Release,
    [switch]$SkipInstall,
    [switch]$Clean
)

$ErrorActionPreference = "Continue"
$PSNativeCommandUseErrorActionPreference = $false

# ─── Environment Setup ───────────────────────────────────────────────
if (Test-Path "C:\Program Files\Eclipse Adoptium\jdk-17.0.20.101-hotspot") {
    $env:JAVA_HOME = "C:\Program Files\Eclipse Adoptium\jdk-17.0.20.101-hotspot"
} else {
    $env:JAVA_HOME = "C:\Program Files\Android\Android Studio\jbr"
}
$env:ANDROID_HOME = "C:\Users\NightOwl\AppData\Local\Android\Sdk"
$env:ANDROID_SDK_ROOT = $env:ANDROID_HOME
$env:PATH = "$env:JAVA_HOME\bin;$env:ANDROID_HOME\platform-tools;$env:ANDROID_HOME\emulator;C:\nodejs\node-v22.15.0-win-x64;$env:PATH"

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$mobileApp   = Join-Path $projectRoot "candycutz-mobile-app"
$androidDir  = Join-Path $mobileApp "android"

Write-Host ""
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host "  CANDYCUTZ — Local Android APK Builder" -ForegroundColor Cyan
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host ""

# ─── Verify Prerequisites ────────────────────────────────────────────
Write-Host "[1/5] Verifying prerequisites..." -ForegroundColor Yellow

$javaVersion = & cmd /c "java -version 2>&1" | Select-Object -First 1
Write-Host "  Java:        $javaVersion" -ForegroundColor Gray

$nodeVersion = & node --version
Write-Host "  Node.js:     $nodeVersion" -ForegroundColor Gray

$adbCheck = & adb devices 2>&1
$deviceCount = ($adbCheck | Select-String "device$" | Measure-Object).Count
Write-Host "  ADB Devices: $deviceCount connected" -ForegroundColor Gray
Write-Host ""

# ─── Expo Prebuild ────────────────────────────────────────────────────
Set-Location $mobileApp

if ($Clean -and (Test-Path $androidDir)) {
    Write-Host "[2/5] Cleaning existing android/ directory..." -ForegroundColor Yellow
    Remove-Item -Recurse -Force $androidDir
}

if (-not (Test-Path $androidDir)) {
    Write-Host "[2/5] Running expo prebuild (generating native Android project)..." -ForegroundColor Yellow
    Write-Host "  This may take a minute on first run..." -ForegroundColor Gray
    & npx expo prebuild --platform android --no-install
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: expo prebuild failed!" -ForegroundColor Red
        exit 1
    }
    Write-Host "  Native Android project generated." -ForegroundColor Green
} else {
    Write-Host "[2/5] android/ directory already exists, skipping prebuild." -ForegroundColor Gray
    Write-Host "  (Use -Clean flag to force regeneration)" -ForegroundColor Gray
}
Write-Host ""

# ─── ADB Reverse Port Forwarding ─────────────────────────────────────
Write-Host "[3/5] Setting up ADB reverse port forwarding (8081 -> Metro, 8000 -> Laravel, 5174 -> Vite)..." -ForegroundColor Yellow
& adb reverse tcp:8081 tcp:8081 2>$null
& adb reverse tcp:8000 tcp:8000 2>$null
& adb reverse tcp:5174 tcp:5174 2>$null
Write-Host "  Port forwarding active: Metro (8081), Laravel (8000), Vite (5174)" -ForegroundColor Green
Write-Host ""

# ─── Gradle Build ─────────────────────────────────────────────────────
Set-Location $androidDir

$buildTask = if ($Release) { "assembleRelease" } else { "assembleDebug" }
$buildType = if ($Release) { "release" } else { "debug" }

Write-Host "[4/5] Building APK ($buildType) via Gradle..." -ForegroundColor Yellow
Write-Host "  First build takes 5-15 minutes. Subsequent builds are faster." -ForegroundColor Gray
Write-Host ""

& .\gradlew.bat $buildTask
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Gradle build failed!" -ForegroundColor Red
    exit 1
}

$apkPath = Join-Path $androidDir "app\build\outputs\apk\$buildType\app-$buildType.apk"

if (Test-Path $apkPath) {
    $apkSize = [math]::Round((Get-Item $apkPath).Length / 1MB, 1)
    Write-Host ""
    Write-Host "  APK built successfully! ($apkSize MB)" -ForegroundColor Green
    Write-Host "  Location: $apkPath" -ForegroundColor Gray
} else {
    Write-Host "ERROR: APK file not found at expected path!" -ForegroundColor Red
    Write-Host "  Expected: $apkPath" -ForegroundColor Red
    exit 1
}
Write-Host ""

# ─── Install on Device ────────────────────────────────────────────────
if (-not $SkipInstall) {
    Write-Host "[5/5] Installing APK on connected device..." -ForegroundColor Yellow
    & adb install -r $apkPath
    if ($LASTEXITCODE -ne 0) {
        Write-Host "WARNING: ADB install failed. You can install manually:" -ForegroundColor Yellow
        Write-Host "  adb install -r `"$apkPath`"" -ForegroundColor Gray
    } else {
        Write-Host "  App installed successfully!" -ForegroundColor Green
        Write-Host ""
        Write-Host "  Launching CandyCutz..." -ForegroundColor Yellow
        & adb shell am start -n com.candycutz.app/.MainActivity
    }
} else {
    Write-Host "[5/5] Skipping install (--SkipInstall flag set)." -ForegroundColor Gray
}

Write-Host ""
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host "  BUILD COMPLETE" -ForegroundColor Green
Write-Host "  APK: $apkPath" -ForegroundColor Gray
Write-Host "=======================================================================" -ForegroundColor Cyan
Write-Host ""
