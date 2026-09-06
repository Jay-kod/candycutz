@echo off
title CandyCutz Customer Mobile App (Expo)
SET "PATH=C:\nodejs\node-v22.15.0-win-x64;%PATH%"
SET "REACT_NATIVE_PACKAGER_HOSTNAME=10.252.94.238"
cd /d "c:\xampp\htdocs\1\candycutz\candycutz-customer-app"
echo ========================================================
echo   CandyCutz Customer Mobile App (Expo SDK 57)
echo   Targeting: Keffi, Nasarawa State, Nigeria
echo ========================================================
echo.
if not exist node_modules (
    echo Installing dependencies...
    call npm install --legacy-peer-deps
)
echo Starting Expo Dev Server...
call npx expo start
pause
