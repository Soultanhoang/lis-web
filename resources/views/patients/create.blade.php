@extends('adminlte::page')

@section('title', 'Thêm mới Bệnh nhân')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Thêm mới bệnh nhân</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form class="form-submit-lock" action="{{ route('patients.store') }}" method="POST">
                        @csrf {{-- Token bảo mật của Laravel --}}
                        
                        {{-- Mã bệnh nhân --}}
                       <div class="form-group">
                            <label for="patient_code">Mã bệnh nhân <span class="text-danger">*</span></label>
                            <input type="text" 
                                    name="patient_code_display" 
                                    class="form-control" 
                                    value="{{ $suggested_code }}" 
                                    readonly 
                                    style="background-color: #e9ecef; font-weight: bold;">
                        </div>

                        {{-- Tên Bệnh nhân --}}
                        <div class="form-group">
                            <label for="full_name">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                   id="full_name" name="full_name" value="{{ old('full_name') }}">
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
                                   id="dob" name="dob" value="{{ old('dob') }}">
                            @error('dob')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Giới tính --}}
                        <div class="form-group">
                            <label for="gender">Giới tính <span class="text-danger">*</span></label>
                            <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender">
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Nam</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Nữ</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Khác</option>
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
                            <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
                        </div>

                        {{-- Số điện thoại --}}
                        <div class="form-group">
                            <label for="phone_number">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                   id="phone_number" name="phone_number" value="{{ old('phone_number') }}">
                            @error('phone_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Lưu</button>
                        <a href="{{ route('patients.index') }}" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).on('submit', 'form.form-submit-lock', function() {
    var btn = $(this).find('button[type="submit"]');
    var originalText = btn.text(); 
    btn.data('original-text', originalText); 
    btn.html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');
    btn.prop('disabled', true);
});
</script>
@endsection