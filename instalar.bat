@echo off
cd /d "%~dp0"
where php >nul 2>nul
if errorlevel 1 goto missing
where composer >nul 2>nul
if errorlevel 1 goto missing
call composer install --no-interaction
if errorlevel 1 goto failure
php instalar.php
if errorlevel 1 goto failure
echo.
echo Pronto. Abra iniciar.bat para executar a Central.
pause
exit /b 0
:missing
echo PHP ou Composer nao foi encontrado. Reabra o terminal apos instalar.
pause
exit /b 1
:failure
echo A instalacao encontrou um erro. Copie a mensagem acima para verificar.
pause
exit /b 1
