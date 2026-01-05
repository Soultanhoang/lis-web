@extends('adminlte::auth.login')

@section('title', 'LIS System - Đăng nhập')

@section('js')
    {{-- (Giữ trống hoặc thêm JS tùy chỉnh nếu cần) --}}
    <script>
    $(document).ready(function() {
        // Kiểm tra xem có session 'login_success' không
        @if(session('logout_success'))
            Swal.fire({
                icon: 'success',
                title: 'Đăng xuất thành công!',
                text: "{{ session('login_success') }}",
                showConfirmButton: false,
                timer: 3000, // Tự tắt sau 3 giây
                toast: true,
                position: 'top', // Hiện ở góc trên phải (kiểu Toast)
                background: '#a6d96a',
            });
        @endif
    });
</script>
@stop

<!-- <!DOCTYPE html>
<html lang="en">

<head>

    
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="iv3O7hivdcHnh7B9YwBUCtFd2CPZWLSDcZlDiywo">

    
    
    
    <title>
                LIS System            </title>

    
        <link rel="stylesheet" href="http://127.0.0.1:8000/vendor/icheck-bootstrap/icheck-bootstrap.min.css">

    
                            <link rel="stylesheet" href="http://127.0.0.1:8000/vendor/fontawesome-free/css/all.min.css">
                <link rel="stylesheet" href="http://127.0.0.1:8000/vendor/overlayScrollbars/css/OverlayScrollbars.min.css">
                <link rel="stylesheet" href="http://127.0.0.1:8000/vendor/adminlte/dist/css/adminlte.min.css">

                                    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
                            
    
    
    
    
    
            
    
    
</head>

<body class="login-page" >

    
        <div class="login-box">

        
        <div class="login-logo">
            <a href="http://127.0.0.1:8000/home">

                
                                    <img src="http://127.0.0.1:8000/img/lis_logo.png"
                         alt="LIS System Logo" height="50">
                
                
                <b>LIS</b> System

            </a>
        </div>

        
        <div class="card card-outline card-primary">

            
                            <div class="card-header ">
                    <h3 class="card-title float-none text-center">
                        Đăng nhập để bắt đầu phiên                     </h3>
                </div>
            
            
            <div class="card-body login-card-body ">
                    <form action="http://127.0.0.1:8000/login" method="post">
        <input type="hidden" name="_token" value="iv3O7hivdcHnh7B9YwBUCtFd2CPZWLSDcZlDiywo">
        
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control "
                value="" placeholder="Email" autofocus>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope "></span>
                </div>
            </div>

                    </div>

        
        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control "
                placeholder="Mật khẩu">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock "></span>
                </div>
            </div>

                    </div>

        
        <div class="row">
            <div class="col-7"> -->
                <!-- <div class="icheck-primary" title="Keep me authenticated indefinitely or until I manually logout">
                    <input type="checkbox" name="remember" id="remember" >

                    <label for="remember">
                        Remember Me
                    </label>
                </div> -->
            <!-- </div>

            <div class="col-12">
                <button type=submit class="btn btn-block btn-flat btn-primary">
                    <span class="fas fa-sign-in-alt"></span>
                    Đăng nhập
                </button>
            </div>
        </div>
    </form>
            </div>

            
                            <div class="card-footer ">
                        
            <p class="my-0"> -->
            <!-- <a href="http://127.0.0.1:8000/password/reset">
                I forgot my password
            </a> -->
        <!-- </p>
    
    
            <p class="my-0">
            <a href="http://127.0.0.1:8000/register">
                Bạn chưa có tài khoản? Đăng ký ngay !
            </a>
        </p>
                    </div>
            
        </div>

    </div>

    
                            <script src="http://127.0.0.1:8000/vendor/jquery/jquery.min.js"></script>
                <script src="http://127.0.0.1:8000/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
                <script src="http://127.0.0.1:8000/vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
                <script src="http://127.0.0.1:8000/vendor/adminlte/dist/js/adminlte.min.js"></script>
            
    
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" ></script>
            
            

    

    
    

    

    
    
    
            
</body>

</html> -->
