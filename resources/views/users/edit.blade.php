@extends('adminlte::page')

@section('title', 'Chỉnh sửa Người dùng')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Chỉnh sửa người dùng</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- Phương thức PUT cho Update --}}

                        {{-- Tên người dùng --}}
                        <div class="form-group">
                            <label for="name">Tên Người dùng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name', $user->name) }}" >
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Email (chỉ hiển thị) --}}
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" value="{{ $user->email }}" readonly>
                        </div>

                        {{-- Chọn Vai trò --}}
                        <div class="form-group">
                            <label for="role">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" >
                                {{-- Lặp qua các vai trò được gửi từ Controller --}}
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>
                                        {{-- Hiển thị tên vai trò --}}
                                        @if($role == 'admin') Admin
                                        @elseif($role == 'doctor') Bác sĩ
                                        @elseif($role == 'technician') Kỹ thuật viên
                                        @else {{ ucfirst($role) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop