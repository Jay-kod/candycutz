@echo off
echo ===================================================
echo Starting CandyCutz Backend and Frontend...
echo ===================================================

:: Start backend in a new window
echo Starting PHP backend on http://localhost:8000 ...
start "CandyCutz Backend (API)" cmd /k "cd barbing-saloon-api && C:\xampp\php\php.exe -S localhost:8000 -t public"

:: Start frontend in a new window
echo Starting Vite frontend on http://localhost:5173 ...
start "CandyCutz Frontend (Web)" cmd /k "cd barbing-saloon-web && npm run dev"

echo Done! Both servers have been launched in separate terminal windows.
pause
