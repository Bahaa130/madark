@echo off
REM Madarek - start the local dev server and open the site.
REM Double-click this file; keep the window open while you work.

cd /d "%~dp0"

echo ==============================================
echo   Madarek  -  Laravel dev server
echo ==============================================
echo.

REM Fail fast on a missing environment file.
if not exist ".env" (
    echo [ERROR] .env not found.
    echo Run:  composer setup
    echo.
    pause
    exit /b 1
)

REM Clear stale caches so route/config changes are picked up.
php artisan optimize:clear >nul 2>&1

REM Stop anything already holding port 8000 so we never fail on EADDRINUSE.
for /f "tokens=5" %%p in ('netstat -ano ^| findstr ":8000" ^| findstr LISTENING') do (
    echo Port 8000 is busy ^(PID %%p^) - stopping it...
    taskkill /F /PID %%p >nul 2>&1
)
timeout /t 1 >nul

echo Starting server on http://localhost:8000
echo Close this window to stop the server.
echo.

start "" http://localhost:8000
php artisan serve --host=localhost --port=8000

echo.
echo Server stopped.
pause