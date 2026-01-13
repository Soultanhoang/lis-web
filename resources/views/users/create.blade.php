@extends('adminlte::page')

@section('title', 'Thêm Người dùng')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Thêm người dùng mới</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                 <div class="card-body">    
                    <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                        {{-- Tên --}}
                        <div class="form-group">
                            <label for="name">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" placeholder="Nhập họ tên" >
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" placeholder="Nhập email" >
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Mật khẩu --}}
                        <div class="form-group">
                            <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Nhập mật khẩu" >
                            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Nhập lại Mật khẩu --}}
                        <div class="form-group">
                            <label for="password_confirmation">Xác nhận Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu" >
                        </div>

                        {{-- Vai trò --}}
                        <div class="form-group">
                            <label for="role">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-control @error('role') is-invalid @enderror" id="role" name="role">
                                <option value="" disabled selected>-- Chọn vai trò --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                        @if($role == 'admin') Admin
                                        @elseif($role == 'doctor') Bác sĩ
                                        @elseif($role == 'technician') Kỹ thuật viên
                                        @else {{ ucfirst($role) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                </form>
                </div>
            </div>
        </div>
    </div>
@stop