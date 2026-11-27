@echo off
cd /d "%~dp0"
echo Abra http://localhost:8000 no navegador.
php -m 2>nul | findstr /i /x "pdo_sqlite" >nul
if not errorlevel 1 (
    php -S localhost:8000 -t site
) else (
    for /f "delims=" %%i in ('where php 2^>nul') do (
        "%%i" -d extension_dir="%%~dpiext" -d extension=pdo_sqlite -S localhost:8000 -t site
        goto fim
    )
    echo PHP nao encontrado. Adicione o PHP ao PATH e tente novamente.
)
:fim
pause
