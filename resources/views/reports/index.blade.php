@extends('adminlte::page')

@section('title', 'Báo cáo Thống kê')

@section('content_header')
    <h1 style="font-family:Arial, Helvetica, sans-serif">Báo cáo thống kê</h1>
@stop

@section('content')
<div class="container-fluid pt-3">
    
    {{-- 1. Bộ lọc ngày --}}
    <form action="{{ route('reports.index') }}" method="GET" class="card card-outline card-primary mb-3">
        <div class="card-body py-2">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label>Từ ngày:</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $start->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label>Đến ngày:</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $end->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Lọc</button>
                </div>
            </div>
        </div>
    </form>

    {{-- 2. Số liệu tổng quan (Info Boxes) --}}
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalPatients }}</h3>
                    <p>Tổng Phiếu Chỉ định</p>
                </div>
                <div class="icon"><i class="fas fa-user-injured"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalCompleted }}</h3>
                    <p>Phiếu Đã Hoàn Thành</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    {{-- Format tiền Việt Nam --}}
                    <h3>{{ number_format($revenue, 0, ',', '.') }} đ</h3>
                    <p>Ước tính Doanh thu</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>

    {{-- 3. Biểu đồ --}}
    <div class="row">
        {{-- Biểu đồ Cột: Khách theo ngày --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Lượng bệnh nhân theo ngày</h3>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Biểu đồ Tròn: Top xét nghiệm --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold text-dark">Top 5 chỉ số phổ biến</h3>
                </div>
                <div class="card-body">
                    <div style="height: 200px; position: relative;">
                        <canvas id="testTypeChart"></canvas>
                    </div>

                    <div class="mt-4">
                        <ul class="list-group list-group-flush">
                            @foreach($testTypeStats as $index => $stat)
                                <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                                    <span>
                                        {{-- Màu chấm tròn tương ứng với mảng màu trong JS --}}
                                        @php
                                            $colors = ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc' ,''];
                                            $color = $colors[$index] ?? '#cccccc';
                                        @endphp
                                        <i class="fas fa-circle mr-1" style="color: {{ $color }}; font-size: 10px;"></i>
                                        {{ $stat->type_name }}
                                    </span>
                                    <span class="badge badge-light border font-weight-bold">{{ $stat->total }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        
        // --- 1. BIỂU ĐỒ CỘT (DAILY) ---
        var dailyLabels = @json($dailyStats->pluck('date'));
        var dailyData   = @json($dailyStats->pluck('total'));

        new Chart(document.getElementById('dailyChart'), {
            type: 'bar', // Hoặc 'line'
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Số lượng phiếu',
                    data: dailyData,
                    backgroundColor: 'rgba(60, 141, 188, 0.9)',
                    borderColor: 'rgba(60, 141, 188, 0.8)',
                    borderWidth: 1
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });

       // --- 2. BIỂU ĐỒ TRÒN (TEST TYPES) ---
        // Lấy danh sách Tên (Labels) và Số lượng (Data) từ PHP
        var typeLabels = @json($testTypeStats->pluck('type_name')); 
        var typeData   = @json($testTypeStats->pluck('total'));

        // Debug lại lần nữa xem hết null chưa
        console.log("Labels sau khi sửa:", typeLabels);

        new Chart(document.getElementById('testTypeChart'), {
            type: 'doughnut', // Biểu đồ vành khuyên
            data: {
                labels: typeLabels, 
                datasets: [{
                    data: typeData,
                    backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    // Cấu hình Chú thích (Legend)
                    legend: {
                        display: true,      // Bắt buộc = true để hiện tên
                        position: 'right',  // Hiện tên bên phải biểu đồ (dễ nhìn hơn)
                        labels: {
                            color: '#333',  // Màu chữ
                            font: { size: 12 }
                        }
                    },
                    // Cấu hình Tooltip (Khi rê chuột vào)
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                // Format: "Tên: Số lượng"
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed;
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection