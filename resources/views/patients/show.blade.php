@extends('adminlte::page')

@section('title', 'Chi tiết Bệnh nhân')

@section('content_header')
    {{-- Lấy tên bệnh nhân để hiển thị tiêu đề cho rõ ràng --}}
    <h1 class="m-0 text-dark">Chi tiết Bệnh nhân: {{ $patient->full_name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- Hiển thị thông tin bệnh nhân dưới dạng chỉ đọc --}}    
                    <div class="form-group">
                        <label>Mã Bệnh nhân:</label>
                        <p class="form-control-static">{{ $patient->patient_code }}</p>
                    </div>

                    <div class="form-group">
                        <label>Họ và tên:</label>
                        <p class="form-control-static">{{ $patient->full_name }}</p>
                    </div>

                    <div class="form-group">
                        <label>Ngày sinh:</label>
                        {{-- Format lại ngày tháng cho dễ đọc --}}
                        <p class="form-control-static">{{ \Carbon\Carbon::parse($patient->dob)->format('d/m/Y') }}</p>
                    </div>

                    <div class="form-group">
                        <label>Giới tính:</label>
                        <p class="form-control-static">
                            @if($patient->gender == 'male')
                                Nam
                            @elseif($patient->gender == 'female')
                                Nữ
                            @else
                                Khác
                            @endif
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại:</label>
                        {{-- Xử lý trường hợp SĐT có thể trống (nullable) --}}
                        <p class="form-control-static">{{ $patient->phone_number ?? 'Chưa cập nhật' }}</p>
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ:</label>
                        {{-- Xử lý trường hợp địa chỉ có thể trống (nullable) --}}
                        <p class="form-control-static">{{ $patient->address ?? 'Chưa cập nhật' }}</p>
                    </div>

                </div>

                <div class="card-footer">
                    {{-- Nút quay lại trang danh sách --}}
                    <a href="{{ route('patients.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop