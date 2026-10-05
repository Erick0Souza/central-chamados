@echo off
cd /d "%~dp0"
if not exist vendor\autoload.php (
 echo Execute instalar.bat primeiro.
 pause
 exit /b 1
)
echo Abra http://127.0.0.1:8000 no navegador.
php artisan serve --host=127.0.0.1 --port=8000
pause
