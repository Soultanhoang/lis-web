@extends('adminlte::page')

@section('title', 'Quản lý Bệnh nhân')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Danh sách bệnh nhân</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    {{-- Nút Thêm mới bệnh nhân --}}
                    <a href="{{ route('patients.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Thêm mới bệnh nhân
                    </a>
                    {{-- Nút Tìm kiếm bệnh nhân --}}
                    <a href="#" class="btn btn-info" data-toggle="modal" data-target="#searchModal">
                        <i class="fa fa-search"></i> Tìm kiếm...
                    </a>
                </div>

                <div class="card-body">
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
                                <th>Mã bệnh nhân</th>
                                <th>Họ & tên</th>
                                <th>Ngày sinh</th>
                                <th>Giới tính</th>
                                <th>Địa chỉ</th>
                                <th>SĐT</th>
                                <th style="width: 230px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($patients as $patient)
                                <tr>
                                    <td class="font-weight-bold">{{ $patient->patient_code }}</td>
                                    <td class="font-weight-bold text-uppercase">{{ $patient->full_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($patient->dob)->format('d/m/Y') }}</td>
                                    <td>{{ $patient->gender == 'male' ? 'Nam' : ($patient->gender == 'female' ? 'Nữ' : 'Khác') }}</td>
                                    <td>{{ $patient->address }}</td>
                                    <td>{{ $patient->phone_number }}</td>
                                    <td>
                                        {{-- Nút xem chi tiết --}}
                                        <!-- <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-success">
                                            <i class="fa fa-eye"></i> Chi tiết
                                        </a> -->
                                        {{-- Nút sửa--}}
                                        <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-edit"></i> Sửa
                                        </a>
                                        {{-- Nút xoá --}}
                                        <form action="{{ route('patients.destroy', $patient) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa bệnh nhân này?');">
                                                <i class="fa fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Hiển thị link phân trang --}}
                    <div class="mt-3">
                        {{ $patients->links() }}
                    </div>

                </div>
            </div>
        </div>

        {{-- Modal Tìm kiếm Bệnh nhân --}}
        <div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document"> 
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="searchModalLabel">Tìm kiếm bệnh nhân</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        
                        {{-- 1. Thanh tìm kiếm trong popup --}}
                        <div class="form-group">
                            <input type="text" id="modalSearchInput" class="form-control" 
                                placeholder="Nhập Mã BN, Tên, hoặc SĐT để tìm kiếm...">
                        </div>

                        <hr>

                        {{-- 2. Bảng dữ liệu kết quả --}}
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Mã bệnh nhân</th>
                                    <th>Họ & tên</th>
                                    <th>SĐT</th>
                                    <th style="width: 160px;">Hành động</th>
                                </tr>
                            </thead>
                            {{-- JS sẽ điền kết quả vào đây --}}
                            <tbody id="modalSearchResultsTable">
                                {{-- Kết quả tìm kiếm vào đây--}}
                            </tbody>
                        </table>

                        <div id="modalPaginationLinks" class="d-flex justify-content-center">
                            {{-- Phân trang JS vẽ vào đây --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        
        // Biến toàn cục để lưu trữ query hiện tại
        let currentQuery = ''; 
        // Biến toàn cục để lưu tổng số trang
        let totalPages = 1; 

        // 1. Hàm để gọi AJAX và cập nhật bảng
        function fetchPatientData(query = '', page = 1) {
            
            currentQuery = query; // Lưu lại query cho các lần lật trang

            $.ajax({
                url: "{{ route('patients.search') }}",
                type: "GET",
                data: { 'query': query, 'page': page },
                success: function(response) { 
                    let tableBody = $('#modalSearchResultsTable');
                    tableBody.empty(); 

                    let patients = response.data;
                    
                    // Cập nhật tổng số trang
                    totalPages = response.last_page; 

                    if (patients.length > 0) {
                        $.each(patients, function(index, patient) {
                            let showUrl = "{{ url('patients') }}/" + patient.id;
                            let editUrl = "{{ url('patients') }}/" + patient.id + "/edit";
                            let row = '<tr>' +
                                '<td>' + patient.patient_code + '</td>' +
                                '<td>' + patient.full_name + '</td>' +
                                '<td>' + (patient.phone_number ? patient.phone_number : 'N/A') + '</td>' +
                                '<td>' +
                                    '<a href="' + showUrl + '" class="btn btn-xs btn-success"><i class="fa fa-eye"></i> Xem chi tiết</a> ' +
                                    '<a href="' + editUrl + '" class="btn btn-xs btn-info"><i class="fa fa-edit"></i> Sửa</a>' +
                                '</td>' +
                                '</tr>';
                            tableBody.append(row);
                        });
                    } else {
                        tableBody.append('<tr><td colspan="4" class="text-center">Không tìm thấy bệnh nhân nào</td></tr>');
                    }
                    
                    // Gọi hàm vẽ phân trang
                    renderPagination(response);
                },
                error: function(xhr) {
                    tableBody.empty().append('<tr><td colspan="4" class="text-center text-danger">Lỗi: ' + xhr.status + '</td></tr>');
                }
            });
        }

        // 2. Hàm vẽ các nút phân trang
        function renderPagination(response) {
            let paginationContainer = $('#modalPaginationLinks');
            paginationContainer.empty(); 

            if (response.last_page > 1) {
                let paginationHtml = '<ul class="pagination">';

                // Nút "Prev"
                paginationHtml += '<li class="page-item ' + (response.current_page == 1 ? 'disabled' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + (response.current_page - 1) + '">Trước</a>' +
                    '</li>';

                // Nút số trang
                for (let i = 1; i <= response.last_page; i++) {
                    paginationHtml += '<li class="page-item ' + (i == response.current_page ? 'active' : '') + '">' +
                        '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a>' +
                        '</li>';
                }

                // Nút "Next"
                paginationHtml += '<li class="page-item ' + (response.current_page == response.last_page ? 'disabled' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + (response.current_page + 1) + '">Sau</a>' +
                    '</li>';

                paginationHtml += '</ul>';
                paginationContainer.append(paginationHtml);
            }
        }

        // 3. Tải dữ liệu ban đầu khi Modal vừa mở
        $('#searchModal').on('show.bs.modal', function (e) {
            $('#modalSearchInput').val(''); 
            fetchPatientData('', 1); 
        });

        // 4. Xử lý sự kiện nhập từ khoá
        $('#modalSearchInput').on('keyup', function() {
            let query = $(this).val();
            fetchPatientData(query, 1); 
        });
        
        // 5. Xử lý khi bấm vào nút lật trang
        $('#modalPaginationLinks').on('click', '.page-link', function(e) {
            e.preventDefault(); // Ngăn link tải lại trang

            let pageLink = $(this);
            let pageItem = pageLink.closest('.page-item'); // Lấy <li> cha

            // Nếu nút này bị 'disabled' hoặc đang 'active' (là trang hiện tại), thì không làm gì cả
            if (pageItem.hasClass('disabled') || pageItem.hasClass('active')) {
                return false; 
            }

            // Lấy số trang từ nút bấm
            let page = pageLink.data('page');
            
            // Nếu số trang hợp lệ (từ 1 đến tổng số trang)
            if (page > 0 && page <= totalPages) {
                 // Gọi lại hàm fetch, giữ nguyên query cũ, chỉ thay đổi số trang
                fetchPatientData(currentQuery, page);
            }
        });
    });
</script>
@stop