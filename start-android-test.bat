@echo off
title CandyCutz Mobile - Android Studio & Device Test Runner
SET "ANDROID_HOME=C:\Users\NightOwl\AppData\Local\Android\Sdk"
SET "PATH=C:\Users\NightOwl\AppData\Local\Android\Sdk\platform-tools;C:\Users\NightOwl\AppData\Local\Android\Sdk\emulator;C:\nodejs\node-v22.15.0-win-x64;%PATH%"
cd /d "%~dp0candycutz-mobile-app"

echo =======================================================================
echo   CANDYCUTZ UNIFIED MOBILE APP - ANDROID RUNNER
echo   Android Studio Emulator / Connected Phone Stream
echo =======================================================================
echo.

echo [1/3] Checking connected Android devices via ADB...
adb devices
echo.

echo [2/3] Configuring ADB reverse port forwarding for local Laravel backend (port 8000)...
adb reverse tcp:8000 tcp:8000
echo.

echo [3/3] Launching Expo Dev Server targeting Android...
echo (Press 'a' anytime in the terminal to re-open on Android)
echo (Press 'r' to reload the app, or 'm' to open developer menu)
echo.
call npx expo start --android

pause
