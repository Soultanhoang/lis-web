@extends('adminlte::page')

@section('title', 'Quản lý Người dùng')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Danh sách người dùng</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    {{-- Nút thêm --}}
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Thêm mới người dùng
                    </a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID người dùng</th>
                                <th>Tên người dùng</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Ngày tạo</th>
                                <th style="width: 90px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role == 'admin') <span class="badge badge-danger">Admin</span>
                                        @elseif($user->role == 'doctor') <span class="badge badge-primary">Bác sĩ</span>
                                        @elseif($user->role == 'technician') <span class="badge badge-info">Kỹ thuật viên</span>
                                        @else <span class="badge badge-secondary">{{ $user->role }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        {{-- Nút Sửa --}}
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-edit"></i> Sửa
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Không có người dùng nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Link phân trang --}}
                    <div class="mt-3">
                        {{ $users->links('pagination::bootstrap-4') }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop