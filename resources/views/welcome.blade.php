<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>LIS System - Chào mừng</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background-color: #f4f6f9; /* Màu nền xám nhạt của AdminLTE */
        }
        /* Căn giữa toàn bộ nội dung */
        .welcome-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .welcome-box {
            width: 400px; /* Độ rộng của hộp */
            padding: 2.5rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            text-align: center;
        }
        .welcome-box img {
            width: 100px; /* Kích thước logo */
            margin-bottom: 1.5rem;
        }
        .welcome-box h1 {
            font-weight: 300; /* Chữ mỏng */
            margin-bottom: 1rem;
        }
        .welcome-box p {
            font-size: 1.1rem;
            color: #6c757d; /* Màu xám */
            margin-bottom: 2rem;
        }
        .welcome-box .btn {
            padding: 0.75rem 1rem;
            font-size: 1.1rem;
        }
    </style>
</head>
<body class="antialiased">

    <div class="welcome-container">
        
        <div class="welcome-box">
            
            {{-- 1. Logo --}}
            <img src="{{ asset('img/lis_logo.png') }}" alt="LIS System Logo">

            {{-- 2. Tên Hệ thống --}}
            <h1>Chào mừng đến với <b>LIS</b> System</h1>
            
            {{-- 3. Mô tả ngắn --}}
            <p>Hệ thống Quản lý Phòng Xét nghiệm</p>

            {{-- 4. Các nút hành động --}}
            <div class="d-grid gap-2">
                @if (Route::has('login'))
                    {{-- Nút Đăng nhập --}}
                    <a href="{{ route('login') }}" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </a>
                @endif

                <!-- @if (Route::has('register'))
                    {{-- Nút Đăng ký --}}
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-block mt-3">
                        Đăng ký
                    </a>
                @endif -->
            </div>

        </div>

    </div>

    {{-- Tải Font Awesome (để có icon cho nút) - Lấy từ AdminLTE --}}
    <script src="{{ asset('vendor/fontawesome-free/js/all.min.js') }}"></script>
</body>
</html>