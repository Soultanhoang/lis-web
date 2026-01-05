@extends('adminlte::page')

@section('title', 'Phiếu chỉ định đã tạo')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Danh sách phiếu chỉ định</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                      {{-- Nút tạo phiếu--}}
                    <a href="{{ route('test_requests.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo phiếu chỉ định
                    </a>
                    {{-- Nút tìm kiếm --}}
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#searchRequestModal">
                        <i class="fa fa-search"></i> Tìm kiếm...
                    </button>
                </div>
                <div class="card-body">
                    {{-- Hiển thị thông báo thành công --}}
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                        <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Mã phiếu chỉ định</th>
                                <th>Bệnh nhân</th>
                                <th>Chẩn đoán</th>
                                <th>Trạng thái</th>
                                <th>Tổng tiền</th>
                                <th>Ngày chỉ định</th>
                                <th style="width:135px">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $request)
                                <tr>
                                    <td class="font-weight-bold">{{ $request->request_code }}</td>
                                    <td>
                                        <span class="font-weight-bold text-uppercase">{{ $request->patient->full_name }}</span> 
                                        <br>
                                        <small class="text-muted">Mã: {{ $request->patient->patient_code }} | Giới tính: {{ $request->patient->gender === 'male' ? 'Nam' : 'Nữ' }} </small>
                                    </td>
                                    <td>{{ $request->diagnosis ?? 'N/A' }}</td>
                                    <td>
                                        @if($request->status == 'pending')
                                            <span class="badge badge-warning">Chờ kết quả</span>
                                        @elseif($request->status == 'processing')
                                            <span class="badge badge-info">Đang xử lý</span>
                                        @elseif($request->status == 'completed')
                                            <span class="badge badge-success">Đã có kết quả</span>
                                        @else
                                            <span class="badge badge-danger">Đã hủy</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($request->total_price, 0, ',', '.') }} VNĐ</td>
                                    <td>{{ $request->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td>
                                    <td>
                                        {{-- Nút xem chi tiết --}}
                                        <a href="{{ route('test_requests.show', $request) }}" class="btn btn-sm btn-success">
                                            <i class="fa fa-eye"></i> Xem chi tiết
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Bạn chưa tạo phiếu chỉ định nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Link phân trang --}}
                    <div class="mt-3">
                        {{ $requests->links('pagination::bootstrap-4') }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modal tìm kiếm phiếu đã chỉ định --}}
    <div class="modal fade" id="searchRequestModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tìm kiếm phiếu chỉ định</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" id="requestModalSearchInput" class="form-control" 
                            placeholder="Nhập Mã Phiếu, Tên Bệnh nhân hoặc Mã BN...">
                    </div>
                    
                    <hr>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Mã phiếu chỉ định</th>
                                <th>Bệnh nhân</th>
                                <th>Ngày tạo</th>
                                <th>Trạng thái</th>
                                <th>Tổng tiền</th>
                                <th style="width: 110px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="requestModalResultsTable">
                            {{-- Kết quả AJAX vào đây--}}
                        </tbody>
                    </table>
                    <div id="requestModalPaginationLinks" class="d-flex justify-content-center">
                        {{-- Phân trang AJAX vào đây --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    
    let currentQuery = ''; 

    // 1. Hàm gọi AJAX
    function fetchRequestData(query = '', page = 1) {
        currentQuery = query; 
        $.ajax({
            url: "{{ route('test_requests.search') }}", // Route mới
            type: "GET",
            data: { 'query': query, 'page': page,},
            success: function(response) { 
                let tableBody = $('#requestModalResultsTable');
                tableBody.empty(); 
                
                let requests = response.data;

                if (requests.length > 0) {
                    $.each(requests, function(index, req) {
                        // URL xem chi tiết
                        let showUrl = "{{ url('test_requests') }}/" + req.id;

                        // Xử lý hiển thị Trạng thái
                        let statusBadge = '';
                        if(req.status == 'pending') {
                            statusBadge = '<span class="badge badge-warning">Chờ kết quả</span>';
                        } else if(req.status == 'processing') {
                             statusBadge = '<span class="badge badge-info">Chờ duyệt</span>';
                        } else if(req.status == 'completed') {
                            statusBadge = '<span class="badge badge-success">Đã có kết quả</span>';
                        } else {
                            statusBadge = '<span class="badge badge-danger">Đã hủy</span>';
                        }

                        // Format ngày
                        let date = new Date(req.created_at).toLocaleString('vi-VN');

                        let row = '<tr>' +
                            '<td>' + req.request_code + '</td>' +
                            '<td>' + req.patient.full_name + '</td>' +
                            '<td>' + date + '</td>' +
                            '<td>' + statusBadge + '</td>' +
                            '<td>' + new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(req.total_price) + '</td>' +
                            '<td>' +
                                '<a href="' + showUrl + '" class="btn btn-xs btn-success">' +
                                    '<i class="fa fa-eye"></i> Xem chi tiết' +
                                '</a>' +
                            '</td>' +
                            '</tr>';
                        tableBody.append(row);
                    });
                } else {
                    tableBody.append('<tr><td colspan="6" class="text-center">Không tìm thấy phiếu nào</td></tr>');
                }
                
                renderPagination(response);
            }
        });
    }

    // 2. Hàm vẽ phân trang 
    function renderPagination(response) {
        let container = $('#requestModalPaginationLinks');
        container.empty(); 
        if (response.last_page > 1) {
            let html = '<ul class="pagination pagination-sm m-0">';
            html += '<li class="page-item ' + (response.current_page == 1 ? 'disabled' : '') + '"><a class="page-link" href="#" data-page="' + (response.current_page - 1) + '"> Trước</a></li>';
            for (let i = 1; i <= response.last_page; i++) {
                html += '<li class="page-item ' + (i == response.current_page ? 'active' : '') + '"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
            }
            html += '<li class="page-item ' + (response.current_page == response.last_page ? 'disabled' : '') + '"><a class="page-link" href="#" data-page="' + (response.current_page + 1) + '"> Sau</a></li>';
            html += '</ul>';
            container.html(html);
        }
    }

    // 3. Load dữ liệu khi mở modal
    $('#searchRequestModal').on('show.bs.modal', function () {
        $('#requestModalSearchInput').val(''); 
        fetchRequestData('', 1); 
    });

    // 4. Xử lý nhập từ khoá 
    $('#requestModalSearchInput').on('keyup', function() {
        fetchRequestData($(this).val(), 1); 
    });

    // 5. Xử lý phân trang
    $(document).on('click', '#requestModalPaginationLinks .page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        if (page && page > 0) fetchRequestData(currentQuery, page);
    });
});
</script>
@stop