@extends('adminlte::page')

@section('title', 'Nhập Kết quả')

@section('content_header')
    @php
    $isCompleted = $testRequest->status === 'completed';
    @endphp
    <div class="d-flex justify-content-between">
        <h1 style="font-family:Arial, Helvetica, sans-serif">{{ $testRequest->status === 'processing' ? 'Nhập kết quả xét nghiệm' : 'Phiếu kết quả' }} 
        <span class="text-muted mr-2" style="font-size: 0.9rem;">(Mã phiếu chỉ định: <b>{{ $testRequest->request_code }}</b> | Bệnh nhân: <b class="text-uppercase">{{ $testRequest->patient->full_name }}</b>)</span>
    </h1>
       {{-- Nút quay lại --}}
       @if($testRequest->status === 'processing')
            <a href="{{ route('test_results.index') }}" class="btn btn-secondary float-left">
                <i class="fa fa-arrow-left"></i> Quay lại
            </a>
        @elseif($testRequest->status === 'completed')
            <a href="{{ route('test_results.completed') }}" class="btn btn-secondary float-left">
                <i class="fa fa-arrow-left"></i> Quay lại
            </a>
        @endif
    </div>
@stop

@section('content')

<div class="container-fluid">
        {{-- Thông báo Thành công --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Thông báo Lỗi (Error) --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Thông báo Cảnh báo (Warning) --}}
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('warning') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Hiển thị lỗi Validate (ví dụ required, numeric...) --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
<form action="{{ route('test_results.update', $testRequest->id) }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-12">
           @foreach($groupedResults as $categoryName => $results)
            {{-- LOGIC MỚI: Lấy danh sách mã mẫu duy nhất trong nhóm này --}}
            @php
                // Lấy tất cả sample_code, lọc trùng, nối lại bằng dấu phẩy
                // Kết quả VD: "251231-1" hoặc "251231-1, 251231-3"
                $uniqueSamples = $results->pluck('sample.sample_code')
                                        ->unique()
                                        ->filter() // Loại bỏ giá trị null
                                        ->implode(', ');
                                        
                // Lấy màu sắc dựa trên loại mẫu (để làm đẹp - tuỳ chọn)
                // Lấy specimen_type của phần tử đầu tiên
                $specimenType = $results->first()->sample->specimen_type ?? 'N/A';
            @endphp

                    <div class="card card-outline card-info mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            
                            {{-- BÊN TRÁI: TÊN NHÓM --}}
                            <h3 class="card-title font-weight-bold">
                                Nhóm: {{ $categoryName }}
                            </h3>
                            {{-- BÊN PHẢI: MÃ MẪU (Đã đưa lên đây) --}}
                            <div>
                                @if($uniqueSamples)
                                    <span class="text-muted mr-2" style="font-size: 0.9rem;">
                                         Loại mẫu: <b>{{ $specimenType }}</b>
                                    </span>
                                    <span class="text-muted mr-2" style="font-size: 0.9rem">Mã mẫu:</span>
                                    <span class="badge badge-warning" style="font-size: 1rem; padding: 8px 12px;">
                                        {{ $uniqueSamples }}
                                    </span>
                                @else
                                    <span class="badge badge-danger">Chưa có mẫu</span>
                                @endif
                            </div>
                            <div>
                                
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="bg-light">
                                        <th style="width: 30%">Tên Xét nghiệm</th>
                                        <th style="width: 20%">Kết quả</th>
                                        <th style="width: 10%">Đơn vị</th>
                                        <th style="width: 15%">Khoảng tham chiếu</th>
                                        <th style="width: 11%">Thời gian có kết quả</th>
                                        <th style="width: 20%">Ghi chú</th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results as $item)
                                        <tr>
                                            {{-- Tên XN --}}
                                            <td class="align-middle">
                                                <span class="font-weight-bold">{{ $item->testType->test_name }}</span>
                                                <br>
                                                <small class="text-muted">{{ $item->testType->test_code }}</small>
                                            </td>
                                            
                                            {{-- Ô Nhập Kết quả --}}
                                            <td>
                                                <input type="text" 
                                                    class="form-control font-weight-bold text-primary result-input" 
                                                    name="results[{{ $item->id }}][result_value]" 
                                                    value="{{ $item->result_value }}"
                                                    {{ $isCompleted ? 'disabled' : '' }}
                                                    autocomplete="off">
                                                    
                                                    {{-- 2. Nút Mở khóa (Chỉ hiện khi ô này đang bị khóa/có dữ liệu) --}}
                                                    <!-- @if($item->result_value)
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-secondary btn-unlock" title="Bấm để sửa lại">
                                                                <i class="fas fa-lock"></i>
                                                            </button>
                                                        </div>
                                                    @endif -->
                                                </div>
                                            </td>
                                            
                                            {{-- Đơn vị --}}
                                            <td class="align-middle">
                                                <span>{{ $item->testType->unit }}</span>
                                            </td>
                                            
                                            {{-- Trị số bình thường --}}
                                            <td class="align-middle">
                                                <span>{{ $item->testType->normal_range }}</span>
                                            </td>

                                            {{-- Thời gian --}}
                                            <td class="align-middle text-center text-secondary">
                                                <span id="time-{{ $item->id }}">
                                                    {{ $item->result_at ? \Carbon\Carbon::parse($item->result_at)->format('H:i d/m/Y') : '-' }}
                                                </span>
                                            </td>

                                            {{-- Ghi chú (Đã bỏ badge mẫu ở đây cho gọn) --}}
                                           <td class="align-middle">
                                                <input type="text" 
                                                    class="form-control" 
                                                    name="results[{{ $item->id }}][notes]" 
                                                    placeholder="Ghi chú..."
                                                    value="{{ $item->note }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
            @endforeach
            @if(!$isCompleted)
                <div class="card">
                    <div class="card-body">
                        <div class="text-right">
                            <button type="submit" name="action" value="save_draft" class="btn btn-default btn-md">
                                <i class="fas fa-save"></i> Lưu tạm (F8)
                            </button>
                            <button type="submit" name="action" value="complete" 
                                    class="btn btn-success btn-md"
                                    onclick="return confirm('Bạn có chắc chắn muốn HOÀN TẤT phiếu này không? Phiếu sẽ chuyển sang trạng thái Đã xong.');">
                                    <i class="fas fa-check-circle"></i> Lưu kết quả (F9)
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</form>
@stop

@section('js')
<script>
$(document).ready(function() {
        // ID của phiếu hiện tại
        var requestId = "{{ $testRequest->id }}";
        
        // URL API (Sử dụng route mà mình đã tạo ở bài trước)
        var url = "{{ route('test_results.latest', $testRequest->id) }}";

        function checkNewResults() {
            $.ajax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        
                        $.each(response.data, function(index, item) {
                            
                            // Tìm input kết quả theo name mới
                            var inputName = "results[" + item.id + "][result_value]";
                            var $input = $('input[name="' + inputName + '"]');

                            // Tìm input ghi chú
                            var noteName = "results[" + item.id + "][notes]";
                            var $noteInput = $('input[name="' + noteName + '"]');

                            if ($noteInput.val() != item.notes) {
                                
                                // Nếu bạn đang đặt chuột vào ô ghi chú để gõ thì ĐỪNG cập nhật (tránh đang gõ bị xóa)
                                if ($noteInput.is(":focus")) return;

                                // Gán đúng giá trị từ DB vào
                                $noteInput.val(item.notes);
                            }

                            // Chỉ update nếu ô đó chưa bị khóa (chưa là machine-result)
                            // HOẶC nếu giá trị mới khác giá trị đang hiển thị (trường hợp máy chạy lại)
                            if (!$input.hasClass('machine-result') || $input.val() != item.result_value) {
                                
                                // Kiểm tra kỹ hơn: Nếu người dùng đang focus vào ô đó thì KHÔNG update vội
                                // (Tránh trường hợp đang gõ số 5 thì máy điền số 6 đè lên)
                                if ($input.is(":focus")) {
                                    return; 
                                }

                                // 1. Điền giá trị
                                $input.val(item.result_value);

                                // 2. KHÓA TRƯỜNG NHẬP
                                // Dùng prop('readonly', true) là chuẩn nhất
                                $input.prop('readonly', true);
                                
                                
                                // 3. Thêm giao diện
                                $input.addClass('machine-result');
                                $input.addClass('just-updated'); // Nháy màu
                                
                                // Xóa hiệu ứng nháy sau 1.5s
                                setTimeout(function() {
                                    $input.removeClass('just-updated');
                                }, 1500);


                                var $timeSpan = $('#time-' + item.id);
                            
                                // Nếu nội dung khác nhau (nghĩa là mới có giờ mới)
                                if ($timeSpan.length > 0 && $timeSpan.text().trim() !== item.formatted_time) {
                                    // Điền giờ mới vào
                                    $timeSpan.text(item.formatted_time);
                                    
                                    // Hiệu ứng nháy màu nhẹ cho thời gian (nếu thích)
                                    $timeSpan.css('color', '#28a745'); // Xanh lá
                                    setTimeout(function() { 
                                        $timeSpan.css('color', ''); // Trả về màu mặc định
                                    }, 1500);
                                }
                                console.log("Đã cập nhật từ máy: " + item.result_value);
                            }
                        });
                    }
                }
            });
        }

        // Chạy ngay 1 lần khi load trang
        checkNewResults();

        // Sau đó cứ 2 giây quét 1 lần (Polling)
        setInterval(checkNewResults, 2000); 

        // Các xử lý phím tắt F8, F9 giữ nguyên như cũ...
        $(document).keydown(function(e) {
            if(e.which == 119) { // F8
                e.preventDefault();
                $('button[value="save_draft"]').click();
            }
            if(e.which == 120) { // F9
                e.preventDefault();
                $('button[value="complete"]').click();
            }

        });

        $('form input[type="text"]').keydown(function(e) {
            
            // Nếu phím bấm là Enter (Mã 13)
            if (e.which === 13) {
                e.preventDefault(); // <--- QUAN TRỌNG NHẤT: Chặn form submit
                
                // Logic: Tự động nhảy sang ô input tiếp theo
                var inputs = $('form input[type="text"]:visible:not([readonly])'); // Chỉ lấy các ô đang hiện và không bị khóa
                var index = inputs.index(this);

                if (index > -1 && index < inputs.length - 1) {
                    // Focus vào ô kế tiếp
                    inputs.eq(index + 1).focus();
                    
                    // (Tuỳ chọn) Bôi đen toàn bộ text trong ô đó để nhập đè cho nhanh
                    inputs.eq(index + 1).select();
                }
                
                return false; // Chặn lan truyền sự kiện
            }
        });
    });

</script>
@stop

@section('css')
<style>
    /* Ô nhập bị khóa do máy chạy */
    .machine-result {
        background-color: #e9ecef !important; /* Màu xám */
        color: #495057;
        cursor: not-allowed;
        font-weight: bold;
    }

    /* Hiệu ứng nháy xanh khi vừa nhận dữ liệu mới */
    .just-updated {
        background-color: #28a745 !important;
        color: #fff !important;
        transition: background-color 0.5s ease;
    }
</style>
@endsection
