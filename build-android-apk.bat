@echo off
title CandyCutz — Android APK Builder
echo.
echo =======================================================================
echo   CANDYCUTZ — Local Android APK Builder
echo   Building native APK from Expo project...
echo =======================================================================
echo.

powershell -ExecutionPolicy Bypass -File "%~dp0build-android-apk.ps1" %*

echo.
pause
