@extends('adminlte::page')

@section('title', 'Chỉnh sửa xét nghiệm')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Chỉnh sửa xét nghiệm</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('test_types.update', $testType) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- Phương thức PUT cho Update --}}

                        {{-- Mã xét nghiệm --}}
                       <div class="form-group">
                            <label for="test_code">Mã xét Nghiệm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('test_code') is-invalid @enderror" 
                                id="test_code" name="test_code" 
                                value="{{ old('test_code', $testType->test_code) }}" > {{-- Thêm "readonly" --}}
                            
                            @error('test_code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Tên xét nghiệm --}}
                        <div class="form-group">
                            <label for="test_name">Tên xét nghiệm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('test_name') is-invalid @enderror" 
                                   id="test_name" name="test_name" 
                                   value="{{ old('test_name', $testType->test_name) }}" >
                            @error('test_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Nhóm --}}
                        <div class="form-group">
                            <label for="category_name">Nhóm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('category_name') is-invalid @enderror" 
                                   id="category_name" name="category_name" 
                                   value="{{ old('category_name', $testType->category_name) }}" >
                            @error('category_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>


                        {{-- Loại mẫu --}}
                        <div class="form-group">
                            <label for="specimen_type">Loại mẫu <span class="text-danger">*</span></label>
                            <select name="specimen_type" id="specimen_type" class="form-control">
                                <option value="">-- Chọn loại mẫu --</option>
                                @foreach(\App\Models\TestType::SPECIMEN_TYPES as $type)
                                    <option value="{{ $type }}" 
                                        {{ (old('specimen_type', $testType->specimen_type) == $type) ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Đơn vị --}}
                        <div class="form-group">
                            <label for="unit">Đơn vị <span class="text-danger">*</span></label>
                            <input type="test" class="form-control @error('unit') is-invalid @enderror" 
                                   id="unit" name="unit" 
                                   value="{{ old('unit', $testType->unit) }}" >
                            @error('unit')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Khoảng tham chiếu --}}
                        <div class="form-group">
                            <label for="normal_range">Khoảng tham chiếu <span class="text-danger">*</span></label>
                            <input type="normal_range" class="form-control @error('normal_range') is-invalid @enderror" 
                                   id="normal_range" name="normal_range" 
                                   value="{{ old('normal_range', $testType->normal_range) }}" >
                            @error('normal_range')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- Giá tiền --}}
                        <div class="form-group">
                            <label for="price">Giá tiền (VNĐ)</label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                   id="price" name="price" 
                                   value="{{ old('price', $testType->price) }}" {{-- Hiển thị giá cũ --}}
                                   min="0" step="1000">
                            @error('price')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                        <a href="{{ route('test_types.index') }}" class="btn btn-secondary">Hủy</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop