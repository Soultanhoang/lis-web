@extends('adminlte::page')

@section('title', 'Tạo Phiếu Chỉ định')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Tạo phiếu chỉ định mới</h1>
@stop

@section('content')
{{-- Giả lập danh sách nhóm để hiển thị (Bạn nên truyền biến này từ Controller sang) --}}
@php
    $categories = ['Sinh hóa', 'Huyết học', 'Nước tiểu', 'Miễn dịch & Vi sinh', 'Khác'];
@endphp

<form class="form-submit-lock" action="{{ route('test_requests.store') }}" method="POST" id="formTestRequest">
    @csrf

    <div class="row">
        {{-- Thông tin phiếu chỉ định --}}
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thông tin Phiếu chỉ định</h3>
                </div>
                <div class="card-body">
                    {{-- Mã Phiếu --}}
                    <div class="form-group">
                        <label for="request_code">Mã Phiếu <span class="text-danger">*</span></label>
                        <input type="text" 
                            name="request_code_display" 
                            class="form-control" 
                            value="{{ $suggested_code }}" 
                            readonly 
                            style="background-color: #e9ecef; font-weight: bold;">
                    </div>

                    {{-- Chẩn đoán --}}
                    <div class="form-group">
                        <label for="diagnosis">Chẩn đoán cận lâm sàng</label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="2">{{ old('diagnosis') }}</textarea>
                    </div>
                    
                    <hr>
                    
                    {{-- 1. Ô chọn bệnh nhân --}}
                    <div class="form-group">
                        <label>Bệnh nhân <span class="text-danger">*</span></label>
                        <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id') }}">

                        <div class="input-group">
                            <input type="text" id="selectedPatientDisplay" class="form-control" 
                                   placeholder="Chưa chọn bệnh nhân..." readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#patientSelectModal">
                                    <i class="fa fa-search"></i> Chọn bệnh nhân
                                </button>
                            </div>
                        </div>

                        @error('patient_id')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Thông tin xét nghiệm --}}
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Chỉ định Xét nghiệm</h3>
                </div>
                <div class="card-body">
                    {{-- 2. Nút thêm xét nghiệm --}}
                    <div class="form-group">
                        <label>Thêm Xét nghiệm <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#testTypeSelectModal">
                            <i class="fa fa-plus"></i> Thêm xét nghiệm
                        </button>
                    </div>

                    <hr>

                    {{-- 3. Bảng các xét nghiệm được chọn --}}
                    <label>Các Xét nghiệm đã chọn:</label>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Mã Xét nghiệm</th>
                                <th>Tên Xét nghiệm</th>
                                <th>Nhóm</th>
                                <th>Đơn giá</th>
                                <th style="width: 50px;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="selectedTestTypesTable">
                            {{-- JS thêm vào đây --}}
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="3" class="text-right">Tổng cộng:</td>
                                <td class="text-right text-danger" id="totalPriceDisplay">0 VNĐ</td>
                                <td class="text-center">
                                    {{-- Nút Xóa Hết --}}
                                    <button type="button" class="btn btn-danger btn-sm" id="btnRemoveAllTests" title="Xóa tất cả"> Xóa</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    @error('test_type_ids')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg btn-block">
                <i class="fa fa-save"></i> Lưu Phiếu chỉ định
            </button>
        </div>
    </div>
</form>

{{-- Modal chọn bệnh nhân --}}
<div class="modal fade" id="patientSelectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chọn Bệnh nhân</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" id="patientModalSearchInput" class="form-control" 
                           placeholder="Nhập Mã BN, Tên, hoặc SĐT để tìm kiếm...">
                </div>
                <hr>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Mã Bệnh nhân</th>
                            <th>Họ tên</th>
                            <th>SĐT</th>
                            <th style="width: 120px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="patientModalResultsTable">
                        {{-- AJAX --}}
                    </tbody>
                </table>
                <div id="patientModalPaginationLinks" class="d-flex justify-content-center">
                    {{-- AJAX --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal chọn xét nghiệm --}}
<div class="modal fade" id="testTypeSelectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Xét nghiệm</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <input type="text" id="testTypeModalSearchInput" class="form-control" 
                           placeholder="Nhập Mã XN hoặc Tên XN để tìm...">
                </div>

                {{-- PHẦN MỚI THÊM: CHECKBOX CHỌN NHÓM --}}
                <div class="form-group border p-2 bg-light rounded">
                    <label class="mb-1 d-block text-dark">Chọn nhóm:</label>
                    <div id="category-checkboxes">
                        @foreach($categories as $cat)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input chk-category-select" 
                                       id="cat_{{ Str::slug($cat) }}" 
                                       value="{{ $cat }}">
                                <label class="custom-control-label font-weight-normal" for="cat_{{ Str::slug($cat) }}">
                                    {{ $cat }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{-- KẾT THÚC PHẦN MỚI --}}

                <hr>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Mã Xét nghiệm</th>
                            <th>Tên Xét nghiệm</th>
                            <th>Nhóm</th>
                            <th>Đơn vị</th>
                            <th>Giá tiền</th>
                            <th style="width: 65px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="testTypeModalResultsTable">
                        {{-- AJAX --}}
                    </tbody>
                </table>
                <div id="testTypeModalPaginationLinks" class="d-flex justify-content-center">
                    {{-- AJAX --}}
                </div>
            </div>
        </div>
    </div>
</div>
@stop


@section('js')
<script>
$(document).ready(function() {
    
    // --- 1. XỬ LÝ BIẾN TOÀN CỤC ---
    let currentTotalPrice = 0; 

    // Hàm format tiền (VNĐ)
    function formatMoney(amount) {
        return amount.toLocaleString('vi-VN') + ' VNĐ';
    }

    // Hàm cập nhật hiển thị tổng tiền
    function updateTotalPriceDisplay() {
        $('#totalPriceDisplay').text(formatMoney(currentTotalPrice));
    }

    // --- 2. HÀM DÙNG CHUNG: THÊM XÉT NGHIỆM VÀO BẢNG ---
    // (Tách ra để dùng được cho cả nút Thêm lẻ và Checkbox nhóm)
    function addTestToTable(id, code, name, category, price) {
        let testPrice = parseFloat(price) || 0;
        let categoryName = category ? category : 'N/A';

        // 1. Kiểm tra trùng
        if ($('#selectedTestTypesTable').find('input[value="' + id + '"]').length > 0) {
            return false; // Đã tồn tại, không thêm nữa
        }
        
        // 2. Cộng giá tiền
        currentTotalPrice += testPrice;
        updateTotalPriceDisplay();

        let priceFormatted = testPrice.toLocaleString('vi-VN');

        let newRow = '<tr data-category="' + categoryName + '">' + // Thêm data-category để dễ tìm xóa
            '<td>' + code + '</td>' +
            '<td>' +
                '<input type="hidden" name="test_type_ids[]" value="' + id + '">' +
                name +
            '</td>' +
            '<td>' + categoryName + '</td>' +
            '<td class="text-right">' + priceFormatted + ' VNĐ</td>' +
            '<td>' +
               '<center><button type="button" class="btn btn-danger btn-xs remove-test-type" data-price="' + testPrice + '">' +
                    '<i class="fa fa-times"></i>' +
                '</button></center>' +
            '</td>' +
        '</tr>';

        $('#selectedTestTypesTable').append(newRow);
        return true;
    }


    // --- 3. XỬ LÝ MODAL BỆNH NHÂN (Giữ nguyên) ---
    let patientQuery = ''; 
    let patientTotalPages = 1;

    function fetchPatientData(query = '', page = 1) {
        patientQuery = query; 
        $.ajax({
            url: "{{ route('patients.search') }}", 
            type: "GET",
            data: { 'query': query, 'page': page },
            success: function(response) { 
                let tableBody = $('#patientModalResultsTable');
                tableBody.empty(); 
                patientTotalPages = response.last_page; 

                if (response.data.length > 0) {
                    $.each(response.data, function(index, patient) {
                        let row = '<tr>' +
                            '<td>' + patient.patient_code + '</td>' +
                            '<td>' + patient.full_name + '</td>' +
                            '<td>' + (patient.phone_number ? patient.phone_number : 'N/A') + '</td>' +
                            '<td>' +
                                '<button type="button" class="btn btn-xs btn-success btn-select-patient" ' +
                                'data-id="' + patient.id + '" ' +
                                'data-name="' + patient.full_name + '">' +
                                'Chọn bệnh nhân</button>' +
                            '</td>' +
                            '</tr>';
                        tableBody.append(row);
                    });
                } else {
                    tableBody.append('<tr><td colspan="4" class="text-center">Không tìm thấy bệnh nhân</td></tr>');
                }
                renderPatientPagination(response);
            }
        });
    }

    function renderPatientPagination(response) {
        let container = $('#patientModalPaginationLinks');
        container.empty(); 
        if (response.last_page > 1) {
            let html = '<ul class="pagination">';
            html += '<li class="page-item ' + (response.current_page == 1 ? 'disabled' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + (response.current_page - 1) + '">Trước</a></li>';
            for (let i = 1; i <= response.last_page; i++) {
                html += '<li class="page-item ' + (i == response.current_page ? 'active' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
            }
            html += '<li class="page-item ' + (response.current_page == response.last_page ? 'disabled' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + (response.current_page + 1) + '">Sau</a></li>';
            html += '</ul>';
            container.append(html);
        }
    }

    $('#patientSelectModal').on('show.bs.modal', function () {
        $('#patientModalSearchInput').val(''); 
        fetchPatientData('', 1); 
    });

    $('#patientModalSearchInput').on('keyup', function() {
        fetchPatientData($(this).val(), 1); 
    });

    $('#patientModalPaginationLinks').on('click', '.page-link', function(e) {
        e.preventDefault(); 
        let item = $(this).closest('.page-item');
        if (item.hasClass('disabled') || item.hasClass('active')) return false; 
        fetchPatientData(patientQuery, $(this).data('page'));
    });

    $(document).on('click', '.btn-select-patient', function() {
        let button = $(this);
        $('#patient_id').val(button.data('id'));
        $('#selectedPatientDisplay').val(button.data('name') + ' (' + button.closest('tr').find('td:first').text() + ')');
        $('#patientSelectModal').modal('hide');
    });

    // --- 4. XỬ LÝ MODAL XÉT NGHIỆM ---
    let testTypeQuery = ''; 
    let testTypeTotalPages = 1;

    function fetchTestTypeData(query = '', page = 1) {
        testTypeQuery = query; 
        $.ajax({
            url: "{{ route('test_types.search') }}", 
            type: "GET",
            data: { 'query': query, 'page': page },
            success: function(response) { 
                let tableBody = $('#testTypeModalResultsTable');
                tableBody.empty(); 
                testTypeTotalPages = response.last_page; 

                if (response.data.length > 0) {
                   $.each(response.data, function(index, testType) {
                            let priceFormatted = (parseFloat(testType.price) || 0).toLocaleString('vi-VN');
                            let row = '<tr>' +
                                '<td>' + testType.test_code + '</td>' +
                                '<td>' + testType.test_name + '</td>' +
                                '<td>' + (testType.category_name ? testType.category_name : 'N/A') + '</td>' +
                                '<td>' + (testType.unit ? testType.unit : 'N/A') + '</td>' +
                                '<td class="text-right">' + priceFormatted + ' VNĐ</td>' +
                                '<td>' +
                                    '<button type="button" class="btn btn-xs btn-success btn-add-test-type" ' +
                                    'data-id="' + testType.id + '" ' +
                                    'data-code="' + testType.test_code + '" ' +
                                    'data-name="' + testType.test_name + '" ' +
                                    'data-category="' + testType.category_name + '" ' +
                                    'data-price="' + testType.price + '">' + 
                                    'Thêm</button>' +
                                '</td>' +
                                '</tr>';
                            tableBody.append(row);
                     });
                } else {
                    tableBody.append('<tr><td colspan="4" class="text-center">Không tìm thấy xét nghiệm</td></tr>');
                }
                renderTestTypePagination(response);
            }
        });
    }

    function renderTestTypePagination(response) {
        let container = $('#testTypeModalPaginationLinks');
        container.empty(); 
        if (response.last_page > 1) {
            let html = '<ul class="pagination">';
            html += '<li class="page-item ' + (response.current_page == 1 ? 'disabled' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + (response.current_page - 1) + '">Trước</a></li>';
            for (let i = 1; i <= response.last_page; i++) {
                html += '<li class="page-item ' + (i == response.current_page ? 'active' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
            }
            html += '<li class="page-item ' + (response.current_page == response.last_page ? 'disabled' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + (response.current_page + 1) + '">Sau</a></li>';
            html += '</ul>';
            container.append(html);
        }
    }

    $('#testTypeSelectModal').on('show.bs.modal', function () {
        $('#testTypeModalSearchInput').val(''); 
        fetchTestTypeData('', 1); 
    });

    $('#testTypeModalSearchInput').on('keyup', function() {
        fetchTestTypeData($(this).val(), 1); 
    });

    $('#testTypeModalPaginationLinks').on('click', '.page-link', function(e) {
        e.preventDefault(); 
        let item = $(this).closest('.page-item');
        if (item.hasClass('disabled') || item.hasClass('active')) return false; 
        fetchTestTypeData(testTypeQuery, $(this).data('page'));
    });

    // --- 5. SỰ KIỆN: NÚT THÊM LẺ (Dùng hàm dùng chung) ---
    $(document).on('click', '.btn-add-test-type', function() {
        let testType = $(this);
        let result = addTestToTable(
            testType.data('id'),
            testType.data('code'),
            testType.data('name'),
            testType.data('category'),
            testType.data('price')
        );
        
        if(!result) {
            alert('Xét nghiệm này đã được chọn!');
        }
    });

    // --- 6. SỰ KIỆN: CHECKBOX CHỌN CẢ NHÓM (Tính năng mới) ---
    $(document).on('change', '.chk-category-select', function() {
        let checkbox = $(this);
        let categoryName = checkbox.val();
        let isChecked = checkbox.is(':checked');

        if (isChecked) {
            // -- LOGIC CHỌN: GỌI AJAX LẤY HẾT TEST CỦA NHÓM --
            
            // Hiển thị loading nhẹ (nếu muốn)
            checkbox.prop('disabled', true); 
            
            // LƯU Ý: Bạn cần tạo Route 'test_types.get_by_category' trong Laravel
            // Trả về JSON: { data: [ {id, test_code, test_name, category_name, price}, ... ] }
            $.ajax({
                url: "/test-types/get-by-category", // <--- CẦN ROUTE NÀY
                type: "GET",
                data: { category: categoryName },
                success: function(response) {
                    checkbox.prop('disabled', false);
                    
                    if (response.data && response.data.length > 0) {
                        let countAdded = 0;
                        $.each(response.data, function(index, item) {
                            let added = addTestToTable(
                                item.id,
                                item.test_code,
                                item.test_name,
                                item.category_name,
                                item.price
                            );
                            if(added) countAdded++;
                        });
                        // Thông báo nhỏ (tuỳ chọn)
                        // console.log("Đã thêm " + countAdded + " xét nghiệm nhóm " + categoryName);
                    }
                },
                error: function() {
                    checkbox.prop('disabled', false);
                    alert('Lỗi khi tải danh sách xét nghiệm của nhóm này. Kiểm tra lại API!');
                    checkbox.prop('checked', false); // Bỏ tick nếu lỗi
                }
            });

        } else {
            // -- LOGIC BỎ CHỌN: XÓA CÁC TEST CỦA NHÓM ĐÓ --
            // Duyệt qua bảng đã chọn, tìm dòng nào có data-category trùng thì xóa
            $('#selectedTestTypesTable tr').each(function() {
                let row = $(this);
                if (row.data('category') === categoryName) {
                    // Lấy giá tiền để trừ
                    let btnRemove = row.find('.remove-test-type');
                    let priceToRemove = parseFloat(btnRemove.data('price')) || 0;
                    
                    currentTotalPrice -= priceToRemove;
                    row.remove();
                }
            });
            
            if (currentTotalPrice < 0) currentTotalPrice = 0;
            updateTotalPriceDisplay();
        }
    });

    // --- 7. SỰ KIỆN: XÓA DÒNG ---
    $(document).on('click', '.remove-test-type', function() {
        let priceToRemove = parseFloat($(this).data('price')) || 0;
        currentTotalPrice -= priceToRemove;
        if (currentTotalPrice < 0) currentTotalPrice = 0;
        updateTotalPriceDisplay();
        
        // Khi xóa tay 1 dòng, nếu dòng đó thuộc 1 nhóm đang check,
        // thì ta có nên bỏ check nhóm đó không? 
        // Logic đơn giản: cứ để nguyên check hoặc bỏ check tuỳ bạn.
        // Ở đây tôi giữ nguyên để code đơn giản.
        
        $(this).closest('tr').remove();
    });

    // --- 8. SỰ KIỆN: NÚT XÓA TẤT CẢ XÉT NGHIỆM ĐÃ CHỌN ---
    $(document).on('click', '#btnRemoveAllTests', function() {
        // 1. Kiểm tra nếu bảng đang trống thì không làm gì
        if ($('#selectedTestTypesTable children').length === 0 && currentTotalPrice === 0) {
            return;
        }
        // 2. Hỏi xác nhận (Tránh bấm nhầm bay sạch dữ liệu)
        if (!confirm('Bạn có chắc chắn muốn xóa TẤT CẢ xét nghiệm đã chọn không?')) {
            return;
        }
        // 3. Xóa sạch các dòng trong tbody
        $('#selectedTestTypesTable').empty();
        // 4. Reset tổng tiền về 0
        currentTotalPrice = 0;
        updateTotalPriceDisplay();
        // 5. (UX) Bỏ tick tất cả các checkbox chọn nhóm trong Modal (nếu đang tick)
        $('.chk-category-select').prop('checked', false);
    });
});

$(document).on('submit', 'form.form-submit-lock', function() {
    var btn = $(this).find('button[type="submit"]');
    var originalText = btn.text(); 
    btn.data('original-text', originalText); 
    btn.html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');
    btn.prop('disabled', true);
});
</script>
@stop