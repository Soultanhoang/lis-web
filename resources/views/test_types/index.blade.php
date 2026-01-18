@extends('adminlte::page')

@section('title', 'Danh mục xét nghiệm')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Danh mục xét nghiệm</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    {{-- Nút thêm mới xét nghiệm --}}
                    <a href="{{ route('test_types.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Thêm mới loại xét nghiệm
                    </a>
                    {{-- Nút tìm kiếm xét nghiệm --}}
                    <a href="#" class="btn btn-info" data-toggle="modal" data-target="#searchModalTestType">
                            <i class="fa fa-search"></i> Tìm kiếm
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

                    {{-- Hiển thị thông báo lỗi --}}
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Mã xét nghiệm</th>
                                <th>Tên xét nghiệm</th>
                                <th>Nhóm</th>
                                <th>Loại mẫu</th>
                                <th>Đơn vị</th>
                                <th>Khoảng tham chiếu</th>
                                <th>Giá tiền</th>
                                <th style="width: 145px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($testTypes as $testType)
                                <tr>
                                    <td class="font-weight-bold">{{ $testType->test_code }}</td>
                                    <td>{{ $testType->test_name }}</td>
                                    <td>{{ $testType->category_name }}</td>
                                    <td>{{ $testType->specimen_type }}</td>
                                    <td>{{ $testType->unit }}</td>
                                    <td>{{ $testType->normal_range }}</td>
                                    <td>{{ number_format($testType->price, 0, ',', '.') }} VNĐ</td>
                                    <td>
                                        {{-- Nút sửa  --}}
                                        <a href="{{ route('test_types.edit', $testType) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-edit"></i> Sửa
                                        </a>
                                        
                                        {{-- Nút xóa --}}
                                        <form action="{{ route('test_types.destroy', $testType) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa xét nghiệm này?');">
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
                        {{ $testTypes->links() }}
                    </div>

                </div>
            </div>
        </div>


        {{-- Modal Tìm kiếm Xét nghiệm --}}
        <div class="modal fade" id="searchModalTestType" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="searchModalLabel">Tìm kiếm danh mục xét nghiệm</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        
                        {{-- Thanh tìm kiếm --}}
                        <div class="form-group">
                            <input type="text" id="modalSearchInputTestType" class="form-control" 
                                placeholder="Nhập Mã XN, Tên XN, hoặc Giá tiền...">
                        </div>
                        <hr>

                        {{-- Bảng kết quả --}}
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Mã xét nghiệm</th>
                                    <th>Tên xét nghiệm</th>
                                    <th>Nhóm</th>
                                    <th>Loại mẫu</th>
                                    <th>Đơn vị</th>
                                    <th>Giá tiền</th>
                                    <th style="width: 75px;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="modalSearchResultsTableTestType">
                                {{-- Kết quả AJAX vào đây--}}
                            </tbody>
                        </table>

                        {{-- Khu vực phân trang --}}
                        <div id="modalPaginationLinksTestType" class="d-flex justify-content-center">
                            {{-- JS phân trang vào đây --}}
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
        
        // Đảm bảo các biến này là duy nhất cho TestType
        let tt_currentQuery = ''; 
        let tt_totalPages = 1; 

        // 1. Hàm gọi AJAX
        function fetchTestTypeData(query = '', page = 1) {
            
            tt_currentQuery = query; 

            $.ajax({
                url: "{{ route('test_types.search') }}",
                type: "GET",
                data: { 'query': query, 'page': page },
                success: function(response) { 
                    let tableBody = $('#modalSearchResultsTableTestType');
                    tableBody.empty(); 

                    let testTypes = response.data;
                    tt_totalPages = response.last_page; 

                    if (testTypes.length > 0) {
                        $.each(testTypes, function(index, testType) {
                            let editUrl = "{{ url('test_types') }}/" + testType.id + "/edit";
                        
                            // Format giá tiền (VD: 50000 -> 50.000)
                            let priceFormatted = parseFloat(testType.price).toLocaleString('vi-VN');

                            let row = '<tr>' +
                                '<td>' + testType.test_code + '</td>' +
                                '<td>' + testType.test_name + '</td>' +
                                '<td>' + (testType.category_name ? testType.category_name : 'N/A') + '</td>' +
                                '<td>' + (testType.specimen_type ? testType.specimen_type : 'N/A') + '</td>' +
                                '<td>' + (testType.unit ? testType.unit : 'N/A') + '</td>' +
                                '<td>' + priceFormatted + ' VNĐ</td>' +
                                '<td>' +
                                    '<a href="' + editUrl + '" class="btn btn-xs btn-info"><i class="fa fa-edit"></i> Sửa</a>' +
                                '</td>' +
                                '</tr>';
                            
                            tableBody.append(row);
                        });
                    } else {
                        tableBody.append('<tr><td colspan="6" class="text-center">Không tìm thấy xét nghiệm nào</td></tr>');
                    }
                    
                    renderTestTypePagination(response);
                },
                error: function(xhr) {
                    tableBody.empty().append('<tr><td colspan="6" class="text-center text-danger">Lỗi: ' + xhr.status + '</td></tr>');
                }
            });
        }

        // 2. Hàm vẽ phân trang
        function renderTestTypePagination(response) {
            let paginationContainer = $('#modalPaginationLinksTestType'); 
            paginationContainer.empty(); 

            if (response.last_page > 1) {
                let paginationHtml = '<ul class="pagination">';
                paginationHtml += '<li class="page-item ' + (response.current_page == 1 ? 'disabled' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + (response.current_page - 1) + '">Trước</a></li>';
                for (let i = 1; i <= response.last_page; i++) {
                    paginationHtml += '<li class="page-item ' + (i == response.current_page ? 'active' : '') + '">' +
                        '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
                }
                paginationHtml += '<li class="page-item ' + (response.current_page == response.last_page ? 'disabled' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + (response.current_page + 1) + '">Sau</a></li>';
                paginationHtml += '</ul>';
                paginationContainer.append(paginationHtml);
            }
        }

        // 3. Tải dữ liệu khi mở Modal
        $('#searchModalTestType').on('show.bs.modal', function (e) { 
            $('#modalSearchInputTestType').val(''); 
            fetchTestTypeData('', 1); 
        });

        // 4. Xử lý nhập từ khoá
        $('#modalSearchInputTestType').on('keyup', function() { 
            let query = $(this).val();
            fetchTestTypeData(query, 1); 
        });
        
        // 5. Xử lý lật trang
        $('#modalPaginationLinksTestType').on('click', '.page-link', function(e) { 
            e.preventDefault(); 
            let pageLink = $(this);
            let pageItem = pageLink.closest('.page-item'); 

            if (pageItem.hasClass('disabled') || pageItem.hasClass('active')) {
                return false; 
            }
            
            let page = pageLink.data('page');
            
            if (page > 0 && page <= tt_totalPages) {
                fetchTestTypeData(tt_currentQuery, page);
            }
        });

    });
</script>
@stop