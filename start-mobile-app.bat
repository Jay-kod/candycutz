@echo off
title CandyCutz Unified Mobile App (Expo SDK 57)
SET "PATH=C:\nodejs\node-v22.15.0-win-x64;%PATH%"
cd /d "%~dp0candycutz-mobile-app"
echo ========================================================
echo   CandyCutz Unified Mobile App (Expo SDK 57)
echo   Single App for Clients, Master Barbers & Staff Desk
echo   Targeting: Keffi Flagship Saloon & Concierge
echo ========================================================
echo.
if not exist node_modules (
    echo Installing dependencies...
    call npm install --legacy-peer-deps
)
echo Starting Expo Dev Server...
call npx expo start
pause
