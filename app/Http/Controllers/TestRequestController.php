<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestRequest; // Bảng cha
use App\Models\TestResult;  // Bảng con (pivot)
use App\Models\Sample;      // Mẫu bệnh phẩm
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  // <-- Quan trọng: Để dùng Transaction
use App\Models\TestType;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TestRequestController extends Controller
{
    /**
     * Hiển thị danh sách phiếu Bác sĩ đã tạo
     */
   public function index()
    {
        // Lấy TOÀN BỘ phiếu, không phân biệt ai tạo
        $requests = TestRequest::with(['patient', 'doctor'])
                                ->latest() // Sắp xếp mới nhất lên đầu
                                ->paginate(10);
                                
        return view('test_requests.index', compact('requests'));
    }
    /**
     * Hiển thị form tạo phiếu mới
     */
    public function create()
    {
            // 1. Tính toán mã dự kiến (Preview)
        $prefix = date('ymd'); 
        
        // Lấy đơn mới nhất (Không cần lock ở đây vì chỉ để xem)
        $latest = TestRequest::where('request_code', 'like', $prefix . '%')
                    ->orderBy('request_code', 'desc')
                    ->first();

        if ($latest) {
            $nextNumber = intval(substr($latest->request_code, 6)) + 1;
        } else {
            $nextNumber = 1;
        }

        // Tạo mã dự kiến: 2512090001
        $suggested_code = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // 2. Truyền sang View
        return view('test_requests.create', compact('suggested_code'));
    }

    /**
     * Lưu phiếu chỉ định mới (HÀM PHỨC TẠP NHẤT)
     */
    public function store(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        // LƯU Ý: Đã bỏ 'request_code' vì hệ thống tự sinh, không tin tưởng mã từ View.
        $validated = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'diagnosis' => 'nullable|string',
            'test_type_ids' => 'required|array|min:1', 
            'test_type_ids.*' => 'required|integer|exists:test_types,id',
        ], [
            'test_type_ids.required' => 'Phải chọn ít nhất một xét nghiệm.',
            'patient_id.required' => 'Phải chọn một bệnh nhân để chỉ định.',
        ]);

        DB::beginTransaction();
        try {
            // --- A. LOGIC SINH MÃ SID (An toàn tuyệt đối) ---
            $prefix = date('ymd'); // Ví dụ: 251209

            // Lock bảng để chặn người khác chèn vào giữa lúc đang tính toán
            $latest = TestRequest::where('request_code', 'like', $prefix . '%')
                        ->orderBy('request_code', 'desc')
                        ->lockForUpdate()
                        ->first();

            // Tính số thứ tự tiếp theo
            $nextNr = $latest ? (intval(substr($latest->request_code, 6)) + 1) : 1;
            
            // Chốt mã cuối cùng: 2512090001
            $finalCode = $prefix . str_pad($nextNr, 4, '0', STR_PAD_LEFT);
            // -----------------------------------------------

            // --- B. TÍNH TỔNG TIỀN ---
            $testTypeIds = $validated['test_type_ids'];
            $totalPrice = TestType::whereIn('id', $testTypeIds)->sum('price');

            // --- C. TẠO PHIẾU CHỈ ĐỊNH (Lưu 1 lần duy nhất) ---
            $testRequest = TestRequest::create([
                'request_code' => $finalCode, // Dùng mã vừa sinh ở bước A
                'patient_id' => $validated['patient_id'],
                'doctor_id' => Auth::id(),
                'diagnosis' => $validated['diagnosis'],
                'status' => 'pending',
                'total_price' => $totalPrice, 
            ]);

            // --- D. TẠO CHI TIẾT KẾT QUẢ (TestResult) ---
            foreach ($testTypeIds as $testTypeId) {
                TestResult::create([
                    'test_request_id' => $testRequest->id,
                    'test_type_id' => $testTypeId,
                    'result_value' => null, // Chưa có kết quả
                ]);
            }

            // Mọi thứ OK -> Lưu vào DB
            DB::commit();

            return redirect()->route('test_requests.index')
                            ->with('success', 'Tạo phiếu chỉ định thành công. Mã phiếu: ' . $finalCode);

        } catch (\Exception $e) {
            // Có lỗi -> Hủy toàn bộ thao tác
            DB::rollBack();
            
            return redirect()->back()
                            ->with('error', 'Lỗi hệ thống: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Hiển thị chi tiết 1 phiếu (cho Bác sĩ)
     */
    public function show(TestRequest $testRequest)
    {
       // Đảm bảo chỉ bác sĩ tạo phiếu mới xem được (bảo mật)
        if ($testRequest->doctor_id !== Auth::id()) {
            abort(403); // Lỗi Cấm truy cập
        }

        // --- THÊM DÒNG NÀY ---
        // Tải tất cả thông tin liên quan: 
        // 1. Tên Bệnh nhân
        // 2. Danh sách kết quả (results)
        // 3. Tên của từng loại xét nghiệm (results.testType)
        $testRequest->load(['patient', 'results.testType']);
        // ------------------

        // File này chúng ta sắp tạo:
        return view('test_requests.show', compact('testRequest'));
    }

    /**
     * Tìm kiếm phiếu chỉ định (AJAX)
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');
        
        $requests = TestRequest::with('patient') // Tải kèm thông tin bệnh nhân
            ->where('doctor_id', Auth::id()) // Chỉ tìm phiếu của bác sĩ đang đăng nhập
            ->where(function($q) use ($query) {
                // 1. Tìm theo Mã phiếu, Trạng thái, Ngày tạo, Tổng tiền
                $q->where('request_code', 'LIKE', "%{$query}%")
                ->orWhere('status', 'LIKE', "%{$query}%")
                ->orWhere('created_at', 'LIKE', "%{$query}%")
                ->orWhere('total_price', 'LIKE', "%{$query}%")
                
                // 2. Tìm theo Tên BN hoặc Mã BN (Dùng whereHas để query bảng quan hệ)
                  ->orWhereHas('patient', function($subQ) use ($query) {
                      $subQ->where('full_name', 'LIKE', "%{$query}%")
                           ->orWhere('patient_code', 'LIKE', "%{$query}%");
                  });
            })
            ->latest()
            ->paginate(5); // Phân trang 5 kết quả
        return response()->json($requests);
    }

    public function getTestsForModal()
    {
        // Lấy tất cả xét nghiệm và GOM NHÓM theo 'category_name'
        // Kết quả sẽ là mảng dạng: ['Sinh hóa' => [item1, item2], 'Huyết học' => [item3]...]
        $grouped_tests = TestType::all()->groupBy('category_name');

        // Trả về view partial (chứa nội dung HTML của modal)
        return view('test_requests.partials.test_selection_list', compact('grouped_tests'));
    }
}