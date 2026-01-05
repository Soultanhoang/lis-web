@extends('adminlte::page')

@section('title', 'Xác nhận Lấy mẫu')

@section('content_header')
    <h1 style="font-family:Arial, Helvetica, sans-serif">Xác nhận lấy mẫu</h1>
@stop

@section('content')
<div class="container-fluid">
    {{-- 1. Thông tin hành chính --}}
    <div class="card card-info card-outline">
        <div class="col-md-12">
             <div class="card-body row">
                    <div class="col-md-6">
                        <h5 class="text-primary font-weight-bold">1. THÔNG TIN BỆNH NHÂN</h5>
                        <table class="table table-sm table-border mb-0">
                            <tr>
                                <td style="width: 120px;">Họ tên:</td>
                                <td><strong class="text-uppercase" style="font-size: 1.1em">{{ $testRequest->patient->full_name }}</strong></td>
                            </tr>
                            <tr>
                                <td>Mã BN:</td>
                                <td>{{ $testRequest->patient->patient_code }}</td>
                            </tr>
                            <tr>
                                <td>Năm sinh:</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($testRequest->patient->dob)->format('d/m/Y') }} 
                                    ({{ \Carbon\Carbon::parse($testRequest->patient->dob)->age }} tuổi)
                                    - 
                                    {{ $testRequest->patient->gender == 'male' ? 'Nam' : 'Nữ' }}
                                </td>
                            </tr>
                            <tr>
                                <td>Địa chỉ:</td>
                                <td>{{ $testRequest->patient->address ?? '...' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-primary font-weight-bold">2. THÔNG TIN LÂM SÀNG</h5>
                        <table class="table table-sm table-border mb-0">
                            <tr>
                                <td style="width: 130px;">Số phiếu:</td>
                                <td><strong>{{ $testRequest->request_code }}</strong></td>
                            </tr>
                            <tr>
                                <td>Bác sĩ chỉ định:</td>
                                <td>{{ $testRequest->doctor->name }}</td>
                            </tr>
                            <tr>
                                <td>Ngày chỉ định:</td>
                                <td>{{ $testRequest->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td>Chẩn đoán:</td>
                                <td>{{ $testRequest->diagnosis ?? '...' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    

    {{-- 2. Form Xác nhận Lấy mẫu --}}
    <form action="{{ route('samples.store') }}" method="POST" id="form-collect-samples">
        @csrf
        
        <div class="card card-success card-outline">
            <div class="card-header">
                <h5 class="text-primary font-weight-bold">3. DANH SÁCH MẪU CẦN LẤY</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px" class="text-center"> 
                                <input type="checkbox" id="checkAll">
                            </th>
                            <th>Mã mẫu</th>
                            <th>Loại Mẫu</th>
                            <th>Trạng thái</th>
                            <th>Ngày lấy mẫu</th>
                            <th style="width: 185px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($testRequest->samples as $sample)
                            <tr>
                                <td class="text-center align-middle">
                                    {{-- Checkbox để chọn mẫu đã lấy --}}
                                    <input type="checkbox" name="sample_ids[]" value="{{ $sample->id }}" class="sample-checkbox" checked>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-warning" style="font-size: 1rem;">{{ $sample->sample_code }}</span>
                                </td>
                                <td class="align-middle font-weight-bold text-primary">
                                    @if ($sample->specimen_type == 'Nước tiểu')
                                        <span class="font-weight-bold text-warning">Nước tiểu</span>
                                    @elseif ($sample->specimen_type == 'Máu toàn phần')
                                        <span class="font-weight-bold text-danger">Máu toàn phần</span>
                                    @elseif ($sample->specimen_type == 'Huyết thanh')
                                        <span class="font-weight-bold text-primary">Huyết thanh</span>
                                    @else
                                        <span class="font-weight-bold text-secondary">Máu toàn phần</span>
                                    @endif
                                <td class="align-middle">
                                    @if ($sample->status == 'pending')
                                        <h5><span class="badge badge-secondary">Chưa lấy</span></h5>
                                    @else ($sample->status == 'received')
                                        <h5><span class="badge badge-success">Đã lấy</span></h5>
                                    @endif
                                </td>
                                <td>
                                     {{ $sample->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i')  }}
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('samples.print_label', $sample) }}" class="btn btn-sm btn-secondary" target="_blank"> {{-- target="_blank" mở tab mới --}}
                                        <i class="fa fa-print"></i> In tem Barcode
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-right">
                 <a href="{{ route('samples.index') }}" class="btn btn-md btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-success btn-md">
                    <i class="fas fa-check-circle"></i> Xác nhận đã lấy mẫu
                </button>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    // Script chọn tất cả checkbox
    $('#checkAll').click(function() {
        $('.sample-checkbox').prop('checked', this.checked);
    });

    $('#form-collect-samples').on('submit', function(e) {
            
            // Đếm số lượng checkbox có class 'sample-checkbox' đang được check
            // (Class này bạn đã có trong code bảng ở các bài trước)
            var checkedCount = $('.sample-checkbox:checked').length;

            // Nếu chưa chọn cái nào (Count = 0)
            if (checkedCount === 0) {
                e.preventDefault(); // <--- LỆNH QUAN TRỌNG: Chặn reload trang
                
                // Hiển thị thông báo (Dùng Alert thường hoặc SweetAlert nếu có)
                
                // Cách 1: Alert mặc định trình duyệt
                alert('Vui lòng chọn ít nhất 1 mẫu để xác nhận!');

                // Cách 2: Nếu bạn dùng SweetAlert (AdminLTE thường có sẵn)
                // /*
                // Swal.fire({
                //     icon: 'warning',
                //     title: 'Chưa chọn mẫu!',
                //     text: 'Vui lòng tích chọn ít nhất 1 ống mẫu để xác nhận.',
                // });
                // */
            }
        });
</script>
@stop