$env:PATH += ";C:\nodejs\node-v22.15.0-win-x64;C:\xampp\php"
$env:NODE = "C:\nodejs\node-v22.15.0-win-x64\node.exe"
$env:EXPO_NODE_PATH = "C:\nodejs\node-v22.15.0-win-x64\node.exe"
adb reverse tcp:8000 tcp:8000
adb reverse tcp:8081 tcp:8081
npx expo start --localhost
