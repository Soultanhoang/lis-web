@extends('adminlte::page')

{{-- Title cứng tại view --}}
@section('title', 'Danh sách chờ lấy mẫu')

@section('content_header')
    <h1 style="font-family:Arial, Helvetica, sans-serif">Danh sách chờ lấy mẫu</h1>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-body">
            
            {{-- Form Tìm kiếm (Giữ nguyên giao diện) --}}
            <form action="{{ route('samples.index') }}" method="GET" class="mb-3" style="width: 520px">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm..." value="{{ request('keyword') }}">
                    <div class="input-group-append">
                        <button class="btn btn-info" type="submit"><i class="fas fa-search"></i> Tìm</button>
                    </div>
                    @if(request('keyword'))
                        <div class="input-group-append">
                            <a href="{{ url()->current() }}" class="btn btn-danger" title="Xóa tìm kiếm">
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
                <thead class="thead-light">
                    <tr>
                        <th style="width: 150px">Mã phiếu chỉ định</th>
                        <th>Bệnh nhân</th>
                        <th>Bác sĩ chỉ định</th>
                        <th>Ngày chỉ định</th>
                        <th style="width: 120px">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Duyệt qua danh sách các phiếu CẦN LẤY MẪU --}}
                    @forelse ($pendingRequests as $request)
                        <tr>
                            <td class="font-weight-bold">{{ $request->request_code }}</td>
                            <td>
                                <span class="font-weight-bold text-uppercase">{{ $request->patient->full_name }}</span> 
                                <br>
                                <small class="text-muted">Mã: {{ $request->patient->patient_code }} | Giới tính: {{ $request->patient->gender === 'male' ? 'Nam' : 'Nữ' }} </small>
                            </td>
                            <td>{{ $request->doctor->name }}</td>
                            <td>{{ $request->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('samples.create', $request->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-vial"></i> Lấy mẫu
                                </a>

                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Không có phiếu chờ lấy mẫu.</td></tr>
                    @endforelse
                </tbody>
            </table>
            
            {{-- Giữ nguyên phân trang --}}
            {{ $pendingRequests->links('pagination::bootstrap-4') }}
        </div>
    </div>
@stop