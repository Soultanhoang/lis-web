@extends('adminlte::page')

@section('title', 'Danh sách Kết quả Đã xong')

@section('content_header')
    <h1 style="font-family:Arial, Helvetica, sans-serif">Danh sách kết quả đã hoàn thành</h1>
@stop

@section('content')
    <div class="card card-success card-outline">
        <div class="card-body">
            
            {{-- Form Tìm kiếm --}}
            <form action="{{ route('test_results.completed') }}" method="GET" class="mb-3" style="width: 520px">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control" 
                           placeholder="Tìm theo Mã Phiếu, Tên BN..." 
                           value="{{ request('keyword') }}">
                    <div class="input-group-append">
                        <button class="btn btn-success" type="submit"><i class="fas fa-search"></i> Tìm</button>
                    </div>
                    @if(request('keyword'))
                        <div class="input-group-append">
                            <a href="{{ route('test_results.completed') }}" class="btn btn-default" title="Xóa tìm kiếm">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </form>

            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 160px">Mã phiếu chỉ định</th>
                        <th>Bệnh nhân</th>
                        <th>Bác sĩ chỉ định</th>
                        <th>Ngày chỉ định</th>
                        <th>Ngày có kết quả</th>
                        <th style="width: 210px" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($completedRequests as $request)
                        <tr>
                            <td class="font-weight-bold">{{ $request->request_code }}</td>
                            
                            <td>
                                <span class="font-weight-bold text-uppercase">{{ $request->patient->full_name }}</span> <br>
                                <small class="text-muted">Mã: {{ $request->patient->patient_code }} | Giới tính: {{ $request->patient->gender === 'male' ? 'Nam' : 'Nữ' }}</small>
                            </td>

                            <td>{{ $request->doctor->name }}</td>
                            <td>
                                {{ $request->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                            </td>
                            {{-- Hiển thị ngày cập nhật cuối cùng (ngày hoàn thành) --}}
                            <td>
                                {{ $request->updated_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-center">
                                {{-- Nút Xem/Sửa lại (Vẫn dùng trang enterResults nhưng dùng icon con mắt) --}}
                                <a href="{{ route('test_results.enter', $request->id) }}" class="btn btn-sm btn-success" title="Xem chi tiết / Chỉnh sửa">
                                    <i class="fas fa-eye"></i> Xem kết quả
                                </a>

                                {{-- Nút In Phiếu (Quan trọng nhất ở trang này) --}}
                                <a href="{{ route('test_results.print', $request->id) }}" target="_blank" class="btn btn-sm btn-secondary" title="In phiếu kết quả">
                                    <i class="fas fa-print"></i> In KQ
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Chưa có phiếu nào hoàn thành.</td></tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="mt-3">
                {{ $completedRequests->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@stop