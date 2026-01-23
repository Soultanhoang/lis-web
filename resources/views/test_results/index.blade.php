@extends('adminlte::page')

@section('title', 'Nhập Kết quả Xét nghiệm')

@section('content_header')
    <h1 style="font-family:Arial, Helvetica, sans-serif"> Danh sách chờ kết quả</h1>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-body">
            
            {{-- Form Tìm kiếm --}}
            <form action="{{ route('test_results.index') }}" method="GET" class="mb-3" style="width: 520px">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control" 
                           placeholder="Tìm theo Mã Phiếu, Tên Bệnh nhân..." 
                           value="{{ request('keyword') }}">
                    <div class="input-group-append">
                        <button class="btn btn-info" type="submit"><i class="fas fa-search"></i> Tìm</button>
                    </div>
                    @if(request('keyword'))
                        <div class="input-group-append">
                            <a href="{{ route('test_results.index') }}" class="btn btn-danger" title="Xóa tìm kiếm">
                                <i class="fas fa-times"></i> Huỷ
                            </a>
                        </div>
                    @endif
                </div>
            </form>
             {{-- Hiển thị thông báo thành công --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="thead-light">
                        <th style="width: 160px">Mã phiếu chỉ định</th>
                        <th>Bệnh nhân</th>
                        <th>Bác sĩ chỉ định</th>
                        <th>Ngày chỉ định</th>
                        <th>Ngày đã lấy mẫu</th>
                        <th class="text-center">Tiến độ</th>
                        <th style="width: 145px" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($processingRequests as $request)
                        <tr>
                            <td class="font-weight-bold">{{ $request->request_code }}</td>
                            <td>
                                <span class="font-weight-bold text-uppercase">{{ $request->patient->full_name }}</span> 
                                <br>
                                <small class="text-muted">Mã: {{ $request->patient->patient_code }} | Giới tính: {{ $request->patient->gender === 'male' ? 'Nam' : 'Nữ' }} </small>
                            </td>

                            <td>{{ $request->doctor->name }}</td>

                            <td>{{ $request->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i')}}</td>
                           
                            <td>
                                @php
                                    // Lấy thời gian collected_at cuối cùng trong các mẫu của phiếu này
                                    $collectedDate = $request->samples->max('collected_at');
                                @endphp

                                @if($collectedDate)
                                    <span>
                                        {{ \Carbon\Carbon::parse($collectedDate)->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i')}}
                                    </span>
                                @else
                                    <span class="text-muted font-italic">Chưa cập nhật</span>
                                @endif
                            </td>

                            {{-- CỘT TIẾN ĐỘ: Đếm số kết quả đã nhập / Tổng số chỉ định --}}
                            <td class="text-center align-middle">
                                @php
                                    $total = $request->testResults->count();
                                    // Đếm số dòng có result_value khác null
                                    $done = $request->testResults->whereNotNull('result_value')->count();
                                    // $percent = $total > 0 ? ($done / $total) * 100 : 0;
                                @endphp
                                <span>{{ $done }}/{{ $total }} chỉ số</span>
                            </td>

                            <td class="text-center align-middle">
                                {{-- Nút chuyển sang trang nhập liệu --}}
                                <a href="{{ route('test_results.enter', $request->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-keyboard"></i> Nhập kết quả
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Không có phiếu nào đang chờ nhập kết quả.</td></tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="mt-3">
                {{ $processingRequests->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@stop