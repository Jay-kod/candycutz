@echo off
setlocal
cd /d "%~dp0"

title CandyCutz Auto-Upload Watcher
echo =======================================================
echo   CandyCutz Auto-Upload Watcher
echo   Repository: https://github.com/Jay-kod/candycutz
echo   Interval:   Checks every 30 seconds
echo   Press Ctrl+C to stop
echo =======================================================
echo.

:loop
git status --porcelain | findstr /R "." >nul 2>&1
if not errorlevel 1 (
    echo [%DATE% %TIME%] Changes detected. Uploading to GitHub...
    call "%~dp0upload-to-github.bat" "chore: auto-sync latest updates"
    echo [%DATE% %TIME%] Auto-upload finished.
    echo.
)

timeout /t 30 /nobreak >nul
goto loop
