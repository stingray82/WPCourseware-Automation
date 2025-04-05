@echo off
setlocal EnableDelayedExpansion

:: Load environment variables from .env file
if exist .env (
    for /f "tokens=1,* delims==" %%A in (.env) do (
        set "value=%%B"
        set "%%A=!value: =!"
    )
) else (
    echo .env file not found. Please create one with required variables.
    pause
    exit /b
)

:: Standardized Variables (based on PLUGIN_SLUG)
set "ZIP_FILE=%PLUGIN_SLUG%.zip"
set "CHANGELOG_PATH=C:\Users\Nathan\Git\rup-changelogs\%PLUGIN_SLUG%.txt"
set "RELEASE_URL=https://reallyusefulplugins.com/releases/%PLUGIN_SLUG%/release.html"
set "PLUGIN_FILE=%PLUGIN_SLUG%\%PLUGIN_SLUG%.php"
set "JSON_LOCATION=%PLUGIN_SLUG%\%JSON_FILE%"

:menu
cls
echo ======================================
echo          Batch Script Menu
echo ======================================
echo 1. Update JSON
echo 2. Create JSON
echo 3. Update Headers
echo 4. Exit
echo ======================================
choice /c 1234 /m "Select an option: "

if errorlevel 4 exit /b
if errorlevel 3 goto update_headers
if errorlevel 2 goto create_json
if errorlevel 1 goto update_json


:update_json
echo Running Update JSON...
php working-php\update-json.php "%JSON_LOCATION%" "%BACKUP_FILE%" "%CHANGELOG_FILE%"
echo Done!
pause
goto menu

:create_json
echo Running Create JSON...
php working-php\create-release-json.php
echo Done!
pause
goto menu

:update_headers
:: Run PHP script to update plugin headers
echo %PLUGIN_FILE%
php -f working-php\update_plugin_headers.php "%PLUGIN_FILE%"
echo Plugin headers updated successfully!
pause
goto menu