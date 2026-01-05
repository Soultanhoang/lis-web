<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestRequest;
use App\Models\TestResult;
use App\Models\TestType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Lấy ngày tháng từ input (Mặc định là tháng này)
        $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $end   = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        // 2. Thống kê Tổng quan (Info Boxes)
        $totalPatients = TestRequest::whereBetween('created_at', [$start, $end])->count();
        $totalCompleted = TestRequest::whereBetween('created_at', [$start, $end])
                                     ->where('status', 'completed')->count();
        
        // Giả sử bạn tính doanh thu dựa trên số chỉ số đã làm * giá tiền
        // (Đây là query nâng cao một chút)
        $revenue = TestResult::join('test_types', 'test_results.test_type_id', '=', 'test_types.id')
                             ->whereBetween('test_results.created_at', [$start, $end])
                             ->whereNotNull('test_results.result_value') // Chỉ tính cái đã có kết quả
                             ->sum('test_types.price');

        // 3. Dữ liệu cho Biểu đồ Tròn: Tỷ lệ các loại xét nghiệm (Gluco, Ure...)
       $testTypeStats = DB::table('test_results')
                            ->join('test_types', 'test_results.test_type_id', '=', 'test_types.id')
                            ->whereBetween('test_results.created_at', [$start, $end])
                            // QUAN TRỌNG: Đặt alias (as type_name) để chắc chắn không bị trùng
                            ->select('test_types.test_name as type_name', DB::raw('count(*) as total'))
                            ->groupBy('test_types.test_name')
                            ->orderByDesc('total')
                            ->limit(5)
                            ->get();

        // 4. Dữ liệu cho Biểu đồ Cột: Số lượng khách theo ngày
        $dailyStats = TestRequest::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
                            ->whereBetween('created_at', [$start, $end])
                            ->groupBy('date')
                            ->get();

        return view('reports.index', compact('start', 'end', 'totalPatients', 'totalCompleted', 'revenue', 'testTypeStats', 'dailyStats'));
    }
}