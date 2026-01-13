@extends('adminlte::page')

@section('title', 'Chi tiết Phiếu chỉ định')

@section('content_header')
    <h1 class="m-0 text-dark" style="font-family:Arial, Helvetica, sans-serif">Chi tiết phiếu 
         {{-- Nút quay lại --}}
            <a href="{{ route('test_requests.index') }}" class="btn btn-secondary float-right">
                <i class="fa fa-arrow-left"></i> Quay lại
            </a>
    </h1>
    
@stop

@section('content')
    <div class="row print-area">
        <div class="row d-none d-print-flex mb-4" style="border-bottom: 2px solid #000; padding-bottom: 10px;">
            <div class="col-12 text-center">
                <h2 class="font-weight-bold mt-3">PHIẾU KẾT QUẢ XÉT NGHIỆM</h2>
            </div>
        </div>
        {{-- Thông tin Bệnh nhân --}}
        <div class="col-md-12">
            <div class="card card-primary">
                <!-- <div class="card-header">
                    <h3 class="card-title">1. Thông tin bệnh nhân</h3>
                </div> -->
                <div class="card-body row">
                    <div class="col-md-6 col-print-6">
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

                    <div class="col-md-6 col-print-6">
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

        {{-- Chi tiết Chỉ định & Kết quả --}}
        <div class="col-md-5 d-print-none">
            <div class="card card-info">
                <div class="card-body">
                    {{-- 2. Danh sách xét nghiệm đã chỉ định --}}
                    <h5 class="text-primary font-weight-bold">3. THÔNG TIN CHI TIẾT CHỈ ĐỊNH</h5>
                    <table class="table table-sm table-bordered mb-3"> 
                            <thead class="thead-light">
                                <tr>
                                    <th>Xét nghiệm chỉ định</th>
                                    <th class="text-right">Đơn giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($testRequest->results as $result)
                                    <tr>
                                        <td>{{ $result->testType->test_name }}</td>
                                        <td class="text-right">
                                            {{ number_format($result->testType->price, 0, ',', '.') }} VNĐ
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">Chưa có xét nghiệm nào được chỉ định cho phiếu này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="" class="text-right"><strong>Tổng cộng :</strong></td>
                                    <td class="text-right">
                                        <strong>{{ number_format($testRequest->total_price, 0, ',', '.') }} VNĐ</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                </div>
            </div>
        </div>

        {{-- Chi tiết Chỉ định & Kết quả --}}
        <div class="col-md-7 col-print-12">
            <div class="card card-success">
                <div class="card-body">
                    {{-- 3. Bảng Kết quả chi tiết --}}
                    <h5 class="text-primary font-weight-bold">4. CHI TIẾT KẾT QUẢ XÉT NGHIỆM</h5>
                    <div class="card-tools text-muted">
                            <small>Ngày có kết quả: {{ $testRequest->updated_at ? $testRequest->updated_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') : '...' }}</small>
                    </div>
                    <table class="table table-bordered table-md">
                        <thead class="thead-light">
                            <tr>
                                <th>Tên Xét nghiệm (Mã)</th>
                                <th>Kết quả</th>
                                <th>Đơn vị</th>
                                <th>Khoảng tham chiếu</th>
                                <th>Nhóm</th>
                                <th class="d-print-none">Ghi chú (KTV)</th> 
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($testRequest->results as $result)
                                <tr>
                                    <td>
                                        {{ $result->testType->test_name }}
                                        ({{ $result->testType->test_code }})
                                    </td>
                                    <td>
                                        <strong>{{ $result->result_value ?? '...' }}</strong>
                                    </td>
                                    <td>{{ $result->testType->unit }}</td>
                                    <td>{{ $result->testType->normal_range }}</td>
                                    <td>{{ $result->testType->category_name }}</td>
                                    <td class="d-print-none">{{ $result->notes ?? 'Không'}}</td> 
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Chưa có kết quả.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5 d-none d-print-flex">
                            <div class="col-6 text-center">
                                {{-- Để trống hoặc chữ ký người lấy mẫu --}}
                            </div>
                            <div class="col-6 text-center">
                                <p class="font-weight-bold">BÁC SĨ</p>
                                <br><br><br>
                                <p class="font-weight-bold">{{ $testRequest->doctor->name }}</p>
                            </div>
                        </div>

    <div class="no-print position-fixed" style="bottom: 20px; right: 20px; z-index: 9999;">
        <button onclick="window.print();" class="btn btn-primary btn-lg shadow rounded-circle" style="width: 60px; height: 60px;">
            <i class="fas fa-print"></i>
        </button>
    </div>
@stop



@section('css')
<style>
    /* 1. Thiết lập khổ giấy A4 */
    @page {
        size: A4;
        margin: 1.5cm 1.5cm 1.5cm 1.5cm; /* Căn lề chuẩn văn bản */
    }

    @media print {
        /* 2. Ẩn các thành phần giao diện Web */
        body {
            background-color: #fff;
            color: #000;
            font-family: "font-family:Arial, Helvetica, sans-serif";
            font-size: 13pt; /* Kích thước chữ chuẩn văn bản */
            line-height: 1.3;
        }

        /* Ẩn thanh bên, header, footer, nút bấm */
        .main-sidebar, .main-header, .main-footer, .no-print, .card-header, .btn, .card-tools {
            display: none !important;
        }

        .content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        /* 3. Tinh chỉnh Layout In ấn */
        .print-area {
            width: 100%;
            display: block;
        }

        /* Xử lý Card để trông giống tờ giấy, bỏ bóng, bỏ viền bo tròn */
        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .card-body {
            padding: 0 !important;
        }

        /* Biến layout cột (col-md-*) thành flex hoặc grid trên giấy để tiết kiệm chỗ */
        
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 10px; /* Tạo khoảng cách nhỏ giữa 2 cột */
        }
        
        /* Layout cho bảng 100% chiều rộng */
        .col-md-8, .col-md-4, .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        /* 4. Tinh chỉnh Bảng (Table) - Phần quan trọng nhất */
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-bottom: 1rem;
            background-color: transparent !important;
        }

        .table thead th {
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid #000 !important; /* Viền đậm dưới tiêu đề */
        }

        /* 5. Kỹ thuật Ngắt trang thông minh */
        thead {
            display: table-header-group; /* Lặp lại tiêu đề bảng nếu in sang trang 2 */
        }
        
        tr {
            page-break-inside: avoid; /* Không xé đôi 1 dòng kết quả sang 2 trang */
        }

        h1, h2, h3, h4, h5 {
            color: #000 !important;
            margin-top: 0;
            margin-bottom: 5px;
            font-weight: bold;
        }

        /* 6. Phần chữ ký (Footer) */
        .signature-section {
            page-break-inside: avoid; /* Giữ nguyên khối chữ ký, không bị cắt */
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        /* Ẩn đường dẫn link (href) khi in */
        a[href]:after {
            content: none !important;
        }

        @media print {
        .d-print-none { display: none !important; }
        .col-print-12 { flex: 0 0 100% !important; max-width: 100% !important; }
    }
    }
</style>
@stop