@echo off
title CandyCutz Barber Staff App (Expo)
SET "PATH=C:\nodejs\node-v22.15.0-win-x64;%PATH%"
SET "REACT_NATIVE_PACKAGER_HOSTNAME=10.252.94.238"
cd /d "c:\xampp\htdocs\1\candycutz\candycutz-barber-app"
echo ========================================================
echo   CandyCutz Barber / Staff Mobile App (Expo SDK 57)
echo   Targeting: Keffi Flagship Saloon Desk
echo ========================================================
echo.
if not exist node_modules (
    echo Installing dependencies...
    call npm install --legacy-peer-deps
)
echo Starting Expo Dev Server...
call npx expo start
pause
