@echo off
setlocal
cd /d "%~dp0"

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

rem Wait 30 seconds using ping (works in both interactive and background non-interactive shells)
ping 127.0.0.1 -n 31 >nul 2>&1
goto loop
