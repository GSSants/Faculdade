@echo off
for /f "delims=" %%i in ('where php') do set PHP_EXE=%%i
for %%i in ("%PHP_EXE%") do set PHP_DIR=%%~dpi
echo Iniciando servidor em http://localhost:8000
"%PHP_EXE%" -d extension_dir="%PHP_DIR%ext" -d extension=pdo_sqlite -d extension=sqlite3 -S localhost:8000
