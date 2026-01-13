<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestRequest;
use App\Models\TestResult;
use Illuminate\Support\Facades\DB;
class TestResultController extends Controller
{
    /**
     * Danh sách phiếu đang chờ nhập kết quả (Status = processing)
     */
    public function index(Request $request)
    {
        // 1. Query: Chỉ lấy phiếu đang ở trạng thái 'processing'
        // Eager load 'testResults' để đếm tiến độ (VD: Đã có 3/10 kết quả)
        $query = TestRequest::with(['patient', 'doctor', 'testResults', 'samples'])
                    ->where('status', 'processing') 
                    ->latest();

        // 2. Tìm kiếm (Copy logic từ SampleController sang)
        if ($request->has('keyword') && $request->keyword != '') {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('request_code', 'like', "%{$keyword}%")
                ->orWhereHas('patient', function ($subQuery) use ($keyword) {
                    $subQuery->where('full_name', 'like', "%{$keyword}%")
                             ->orWhere('patient_code', 'like', "%{$keyword}%");
                });
            });
        }

        $processingRequests = $query->paginate(10)->appends(['keyword' => $request->keyword]);

        return view('test_results.index', compact('processingRequests'));
    }

    /**
     * Form nhập kết quả cho 1 phiếu
     */
    public function enterResults($test_request_id)
    {
        // 1. Lấy thông tin phiếu và các kết quả con
        $testRequest = TestRequest::with([
            'patient', 
            'doctor', 
            'testResults.testType', 
            'testResults.sample'
        ])->findOrFail($test_request_id);

        // 2. Gom nhóm kết quả theo Tên nhóm xét nghiệm (Category Name)
        $groupedResults = $testRequest->testResults->groupBy(function($item) {
            return $item->testType->category_name ?? 'Khác';
        });

        return view('test_results.enter', compact('testRequest', 'groupedResults'));
    }

    /**
     * Xử lý Lưu kết quả
     */
    public function updateResults(Request $request, $test_request_id)
    {
        $testRequest = TestRequest::findOrFail($test_request_id);

        // Lấy dữ liệu gửi lên (Lúc này là mảng đa chiều)
        $inputs = $request->input('results', []); 
        $action = $request->input('action'); // 'save_draft' hoặc 'complete'

        // --- 1. KIỂM TRA DỮ LIỆU RỖNG ---
        // lọc xem có ít nhất một ô 'result_value' nào được nhập không
        $hasData = false;
        foreach ($inputs as $data) {
            // Kiểm tra nếu có result_value và nó không rỗng
            if (isset($data['result_value']) && trim($data['result_value']) !== '') {
                $hasData = true;
                break;
            }
        }

        // Nếu không có dữ liệu nào được nhập -> Báo lỗi
        if (!$hasData) {
            return back()->with('warning', 'Bạn chưa nhập kết quả nào để lưu.');
        }

        DB::beginTransaction();
        try {
            $now = now();
            $technicianId = \Illuminate\Support\Facades\Auth::id();

            // --- 2. CẬP NHẬT KẾT QUẢ ---
            foreach ($inputs as $resultId => $data) {
                // $data lúc này là mảng: ['result_value' => '...', 'notes' => '...']
                
                // Lấy giá trị cụ thể
                $val = $data['result_value'] ?? null;
                $note = $data['notes'] ?? null;

                // Chỉ lưu nếu giá trị $val không rỗng
                if ($val !== null && trim($val) !== '') {
                    $testResult = TestResult::find($resultId);

                    // Đảm bảo kết quả thuộc đúng phiếu này
                    if ($testResult && $testResult->test_request_id == $testRequest->id) {
                        $testResult->update([
                            'result_value'  => $val,
                            'notes'          => $note,
                            'technician_id' => $technicianId,
                            'result_at'     => $now,
                        ]);
                    }
                }
            }

            // --- 3. XỬ LÝ TRẠNG THÁI PHIẾU ---
            if ($action === 'complete') {
                // Nếu hoàn tất, kiểm tra xem còn chỉ số nào thiếu không
                $pendingCount = $testRequest->testResults()->whereNull('result_value')->count();

                if ($pendingCount > 0) {
                    DB::rollBack();
                    return back()->withInput()->with('error', "Không thể Hoàn tất! Còn thiếu $pendingCount chỉ số chưa nhập.");
                }

                $testRequest->update(['status' => 'completed']);
                $message = 'Đã hoàn tất phiếu xét nghiệm!';
            } else {
                // Nếu lưu tạm
                if ($testRequest->status !== 'completed') {
                    $testRequest->update(['status' => 'processing']);
                }
                $message = 'Đã lưu tạm kết quả.';
            }

            DB::commit();

            return redirect()->route('test_results.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * API trả về JSON danh sách kết quả (Dùng cho AJAX Polling)
     */
    public function getLatestResults($test_request_id)
    {
        // 1. Lấy dữ liệu gốc
        $results = TestResult::where('test_request_id', $test_request_id)
                        ->select('id', 'result_value', 'result_at', 'technician_id', 'notes')
                        ->whereNotNull('result_value')
                        ->get();

        $data = $results->map(function($item) {
            return [
                'id' => $item->id,
                'result_value' => $item->result_value,
                
                // Format ngày giờ
                'formatted_time' => $item->result_at 
                    ? \Carbon\Carbon::parse($item->result_at)->setTimezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y') : '',

                'technician_id' => $item->technician_id,

                'notes'=>$item->notes,
            ];
        });

        // 3. Trả về biến $data 
        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Danh sách phiếu ĐÃ HOÀN THÀNH (Lịch sử)
     */
    public function completedList(Request $request)
    {
        // 1. Query: Chỉ lấy phiếu 'completed'
        // Sắp xếp theo updated_at (thời điểm hoàn thành) giảm dần
        $query = TestRequest::with(['patient', 'doctor', 'testResults'])
                    ->where('status', 'completed')
                    ->latest('updated_at'); 

        // 2. Tìm kiếm (Giống hệt các phần trước)
        if ($request->has('keyword') && $request->keyword != '') {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('request_code', 'like', "%{$keyword}%")
                ->orWhereHas('patient', function ($subQuery) use ($keyword) {
                    $subQuery->where('full_name', 'like', "%{$keyword}%")
                             ->orWhere('patient_code', 'like', "%{$keyword}%");
                });
            });
        }

        $completedRequests = $query->paginate(10)->appends(['keyword' => $request->keyword]);

        return view('test_results.completed', compact('completedRequests'));
    }

    /**
     * Hàm in kết quả (Placeholder - Để tạm return text)
     */
    public function printResult($id)
    {
        return "Chức năng In PDF cho phiếu ID: $id đang được xây dựng...";
    }
}
