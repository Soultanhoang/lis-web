@extends('adminlte::page')

@section('title', 'Trang chủ')

@section('content_header')
    <h1 style="font-family:Arial, Helvetica, sans-serif">Bảng điều khiển</h1>
@stop

@section('content')
    <div class="row">

        {{-- === DÀNH CHO BÁC SĨ (VÀ ADMIN) === --}}
        @can('is-doctor-or-admin')
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>Bệnh nhân</h3>
                        <p>Quản lý hồ sơ bệnh nhân</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <a href="{{ route('patients.index') }}" class="small-box-footer">
                        Truy cập <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endcan

        {{-- === DÀNH RIÊNG CHO BÁC SĨ === --}}
        @can('is-doctor')
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>Tạo Phiếu</h3>
                        <p>Tạo phiếu chỉ định mới</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <a href="{{ route('test_requests.create') }}" class="small-box-footer">
                        Truy cập <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>Phiếu đã tạo</h3>
                        <p>Xem lịch sử phiếu chỉ định</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <a href="{{ route('test_requests.index') }}" class="small-box-footer">
                        Truy cập <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endcan

        {{-- === DÀNH RIÊNG CHO KỸ THUẬT VIÊN === --}}
        @can('is-technician')
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>Danh sách chờ lấy mẫu</h3>
                        <p>Lấy mẫu bệnh phẩm</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-vial"></i>
                    </div>
                    <a href="{{ route('samples.index') }}" class="small-box-footer">
                        Truy cập <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
             <div class="col-lg-4 col-md-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>Danh sách chờ kết quả</h3>
                        <p>Nhập kết quả xét nghiệm</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-fw fa-keyboard"></i>
                    </div>
                    <a href="{{ route('test_results.index') }}" class="small-box-footer">
                        Truy cập <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>Danh sách kết quả đã xong</h3>
                        <p>Xem các kết quả đã hoàn thành</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-fw fa-check-circle"></i>
                    </div>
                    <a href="{{ route('test_results.completed') }}" class="small-box-footer">
                        Truy cập <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endcan

        {{-- === DÀNH RIÊNG CHO ADMIN === --}}
        @can(abilities: 'is-admin')
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>Người dùng</h3>
                        <p>Quản lý vai trò & tài khoản</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <a href="{{ route('users.index') }}" class="small-box-footer">
                        Truy cập <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>Danh mục xét nghiệm</h3>
                        <p>Quản lý danh mục xét nghiệm</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <a href="{{ route('test_types.index') }}" class="small-box-footer">
                        Truy cập <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>Báo cáo thống kê</h3>
                        <p>Xem báo cáo, thống kê</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-area"></i>
                    </div>
                    <a href="{{ route('reports.index') }}" class="small-box-footer">
                        Truy cập <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        @endcan

    </div>
@stop

@section('css')
    {{-- (Giữ trống hoặc thêm CSS tùy chỉnh nếu cần) --}}
@stop


@section('js')
    {{-- (Giữ trống hoặc thêm JS tùy chỉnh nếu cần) --}}
    <script>
    $(document).ready(function() {
        // Kiểm tra xem có session 'login_success' không
        var loginMessage = "{{ session('login_success') }}"; 

        if (loginMessage.trim() !== "") { // Kiểm tra khác rỗng
            Swal.fire({
                icon: 'success',
                title: 'Đăng nhập thành công!',
                text: loginMessage,
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top',
                background: '#a6d96a',
            });
        }
    });
</script>
@stop