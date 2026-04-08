@echo off
chcp 65001 >nul
title RankReport Pro — Setup

echo.
echo ╔══════════════════════════════════════════════════╗
echo ║        RankReport Pro — Cài đặt lần đầu         ║
echo ╚══════════════════════════════════════════════════╝
echo.

:: ── Xác định thư mục hiện tại ────────────────────────────────────────────
set "APP_DIR=%~dp0"
set "APP_DIR=%APP_DIR:~0,-1%"
set "PARENT_DIR=%APP_DIR%\.."

echo Thư mục project: %APP_DIR%
echo.

:: ── Kiểm tra Laragon / PHP ────────────────────────────────────────────────
where php >nul 2>&1
if errorlevel 1 (
    echo [LỖI] Không tìm thấy PHP trong PATH.
    echo.
    echo Hãy thử một trong hai cách:
    echo   1. Mở Laragon - bật Auto Add Path - rồi chạy lại file này
    echo   2. Hoặc mở Terminal trong Laragon rồi chạy: setup.bat
    echo.
    pause & exit /b 1
)

where composer >nul 2>&1
if errorlevel 1 (
    echo [LỖI] Không tìm thấy Composer trong PATH.
    echo   Tải tại: https://getcomposer.org/Composer-Setup.exe
    pause & exit /b 1
)

echo PHP version:
php --version
echo.

:: ── Bước 1: Tạo Laravel project mới (khung) rồi copy code vào ────────────
echo [1/9] Kiểm tra Laravel framework...
if exist "%APP_DIR%\artisan" (
    echo     artisan đã tồn tại, bỏ qua tạo mới.
    goto :skip_create
)

echo     Tạo Laravel project khung tại thư mục tạm...
cd /d "%PARENT_DIR%"
call composer create-project laravel/laravel rankreport-tmp --no-interaction --quiet
if errorlevel 1 (
    echo [LỖI] Không tạo được Laravel project. Kiểm tra kết nối internet.
    pause & exit /b 1
)

echo     Copy Laravel framework files sang rankreport-pro...
:: Copy tất cả file của Laravel (không ghi đè file đã có trong rankreport-pro)
for %%F in (artisan bootstrap package.json phpunit.xml vite.config.js) do (
    if exist "%PARENT_DIR%\rankreport-tmp\%%F" (
        if not exist "%APP_DIR%\%%F" copy /Y "%PARENT_DIR%\rankreport-tmp\%%F" "%APP_DIR%\%%F" >nul
    )
)

:: Copy folders cần thiết (không ghi đè)
if not exist "%APP_DIR%\bootstrap" xcopy /E /Q /I "%PARENT_DIR%\rankreport-tmp\bootstrap" "%APP_DIR%\bootstrap" >nul
if not exist "%APP_DIR%\storage"   xcopy /E /Q /I "%PARENT_DIR%\rankreport-tmp\storage"   "%APP_DIR%\storage"   >nul
if not exist "%APP_DIR%\tests"     xcopy /E /Q /I "%PARENT_DIR%\rankreport-tmp\tests"     "%APP_DIR%\tests"     >nul

:: Merge composer.json (dùng file của rankreport-pro, chỉ bổ sung autoload chuẩn)
:: File composer.json của project đã đúng, không cần overwrite

:: Xóa thư mục tạm
rmdir /S /Q "%PARENT_DIR%\rankreport-tmp"
echo     Done.

:skip_create

cd /d "%APP_DIR%"

:: ── Bước 2: composer install ──────────────────────────────────────────────
echo.
echo [2/9] Cài đặt PHP dependencies (composer install)...
call composer install --no-interaction --prefer-dist --optimize-autoloader
if errorlevel 1 ( echo [LỖI] composer install thất bại & pause & exit /b 1 )

:: ── Bước 3: .env ─────────────────────────────────────────────────────────
echo.
echo [3/9] Tạo file .env...
if not exist "%APP_DIR%\.env" (
    copy "%APP_DIR%\.env.example" "%APP_DIR%\.env" >nul
    echo     Đã tạo .env
) else (
    echo     .env đã tồn tại, bỏ qua.
)

:: ── Bước 4: APP_KEY ───────────────────────────────────────────────────────
echo.
echo [4/9] Tạo APP_KEY...
php artisan key:generate --ansi

:: ── Bước 5: storage symlink ───────────────────────────────────────────────
echo.
echo [5/9] Tạo storage symlink...
php artisan storage:link

:: ── Hướng dẫn tạo database ───────────────────────────────────────────────
echo.
echo ══════════════════════════════════════════════════════
echo QUAN TRỌNG — Tạo database trước khi tiếp tục:
echo.
echo   Cách 1 (nhanh): Mở Laragon - nhấn chuột phải vào
echo     biểu tượng Laragon trong System Tray - chọn
echo     MySQL - HeidiSQL - tạo DB: rankreport_pro
echo.
echo   Cách 2: Vào http://localhost/phpmyadmin
echo     - Chọn "New" - nhập: rankreport_pro - Create
echo.
echo   Nếu MySQL của bạn có mật khẩu, hãy sửa .env:
echo     DB_PASSWORD=your_password
echo.
echo   Laragon mặc định: DB_USERNAME=root, DB_PASSWORD=
echo ══════════════════════════════════════════════════════
echo.
pause

:: ── Bước 6: migrate ───────────────────────────────────────────────────────
echo [6/9] Chạy migrations...
php artisan migrate --ansi
if errorlevel 1 (
    echo.
    echo [LỖI] migrate thất bại.
    echo Kiểm tra lại:
    echo   - Laragon đang chạy và MySQL đã bật
    echo   - DB rankreport_pro đã được tạo
    echo   - DB_USERNAME/DB_PASSWORD trong .env đúng
    echo.
    pause & exit /b 1
)

:: ── Bước 7: seed ──────────────────────────────────────────────────────────
echo.
echo [7/9] Chạy Seeder (tạo demo data)...
php artisan db:seed --ansi
if errorlevel 1 ( echo [WARN] db:seed có lỗi, nhưng tiếp tục... )

:: ── Bước 8: cache optimize ────────────────────────────────────────────────
echo.
echo [8/9] Tối ưu cache...
php artisan config:clear
php artisan view:clear
php artisan route:clear

:: ── Bước 9: tạo queue tables (session + cache + jobs) ─────────────────────
echo.
echo [9/9] Tạo bảng session/cache/queue...
php artisan migrate --ansi 2>nul

echo.
echo ╔══════════════════════════════════════════════════╗
echo ║              ✅  CÀI ĐẶT HOÀN TẤT!              ║
echo ╠══════════════════════════════════════════════════╣
echo ║  Nhấn phím bất kỳ để khởi động server...        ║
echo ║                                                  ║
echo ║  Sau khi server chạy, mở trình duyệt:           ║
echo ║  🌐  http://localhost:8000                      ║
echo ║                                                  ║
echo ║  Tài khoản đăng nhập:                           ║
echo ║  📧  admin@rankreport.pro                       ║
echo ║  🔑  password                                   ║
echo ╚══════════════════════════════════════════════════╝
echo.
pause >nul

php artisan serve
