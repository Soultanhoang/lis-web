@extends('adminlte::page')

@section('title', 'Chỉnh sửa Bệnh nhân')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Chỉnh sửa bệnh nhân</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('patients.update', $patient) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- Phương thức PUT cho Update --}}

                        {{-- Mã bệnh nhân --}}
                       <div class="form-group">
                            <label for="patient_code">Mã bệnh nhân <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('patient_code') is-invalid @enderror" 
                                id="patient_code" name="patient_code" 
                                value="{{ old('patient_code', $patient->patient_code) }}" readonly> {{-- Thêm "readonly" --}}
                            
                            @error('patient_code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Tên Bệnh nhân --}}
                        <div class="form-group">
                            <label for="full_name">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                   id="full_name" name="full_name" 
                                   value="{{ old('full_name', $patient->full_name) }}" >
                            @error('full_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Ngày sinh --}}
                        <div class="form-group">
                            <label for="dob">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('dob') is-invalid @enderror" 
                                   id="dob" name="dob" 
                                   value="{{ old('dob', $patient->dob) }}" >
                            @error('dob')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Giới tính --}}
                        <div class="form-group">
                            <label for="gender">Giới tính <span class="text-danger">*</span></label>
                            <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" >
                                <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>Nam</option>
                                <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>Nữ</option>
                                <option value="other" {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('gender')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        {{-- Địa chỉ --}}
                        <div class="form-group">
                            <label for="address">Địa chỉ</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="{{ old('address', $patient->address) }}">
                        </div>

                        {{-- Số điện thoại --}}
                        <div class="form-group">
                            <label for="phone_number">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                   id="phone_number" name="phone_number" value="{{ old('phone_number', $patient->phone_number) }}" >
                            @error('phone_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                        <a href="{{ route('patients.index') }}" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop