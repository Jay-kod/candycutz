@echo off
set "PATH=%PATH%;C:\nodejs\node-v22.15.0-win-x64;C:\xampp\php"
set "NODE=C:\nodejs\node-v22.15.0-win-x64\node.exe"
set "EXPO_NODE_PATH=C:\nodejs\node-v22.15.0-win-x64\node.exe"
title CandyCutz Mobile App
adb reverse tcp:8000 tcp:8000
adb reverse tcp:8081 tcp:8081
npx expo start --localhost
