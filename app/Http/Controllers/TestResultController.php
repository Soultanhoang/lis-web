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
     * HIỂN THỊ GIAO DIỆN NHẬP KẾT QUẢ
     */
    public function enterResults($test_request_id)
    {
        // 1. Lấy thông tin phiếu và các kết quả con
        // Eager load sâu: testResults -> testType (để lấy tên, đơn vị, khoảng thường)
        // Eager load: testResults -> sample (để hiển thị barcode mẫu tương ứng)
        $testRequest = TestRequest::with([
            'patient', 
            'doctor', 
            'testResults.testType', 
            'testResults.sample'
        ])->findOrFail($test_request_id);

        // 2. Gom nhóm kết quả theo Tên nhóm xét nghiệm (Category Name)
        // Để hiển thị đẹp: Sinh hóa riêng, Huyết học riêng
        $groupedResults = $testRequest->testResults->groupBy(function($item) {
            return $item->testType->category_name ?? 'Khác';
        });

        return view('test_results.enter', compact('testRequest', 'groupedResults'));
    }

    /**
     * XỬ LÝ LƯU KẾT QUẢ
     */
    // public function updateResults(Request $request, $test_request_id)
    // {
    //     $testRequest = TestRequest::findOrFail($test_request_id);

    //     // Lấy dữ liệu từ form
    //     $inputs = $request->input('results', []);
    //     $notes  = $request->input('notes', []);
    //     $action = $request->input('action'); // 'save_draft' hoặc 'complete'

    //     // 1. LỌC DỮ LIỆU RỖNG (Để cho phép lưu tạm từng phần)
    //     $cleanInputs = array_filter($inputs, function($value) {
    //         return $value !== null && trim($value) !== '';
    //     });

    //     // Nếu không nhập gì cả -> Báo lỗi
    //     if (empty($cleanInputs)) {
    //         return back()->with('warning', 'Bạn chưa nhập kết quả nào để lưu.');
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $now = now();
    //         $technicianId = \Illuminate\Support\Facades\Auth::id(); // Lấy ID người đang đăng nhập

    //         // 2. UPDATE KẾT QUẢ (Kết hợp logic bảo mật của bạn)
    //         foreach ($cleanInputs as $resultId => $value) {
    //             $testResult = TestResult::find($resultId);

    //             // Check bảo mật: Đảm bảo kết quả thuộc đúng phiếu này
    //             if ($testResult && $testResult->test_request_id == $testRequest->id) {
    //                 $testResult->update([
    //                     'result_value'  => $value,
    //                     'note'          => $notes[$resultId] ?? null,
    //                     'technician_id' => $technicianId, // <--- Của bạn (Rất tốt)
    //                     'result_at'     => $now,          // <--- Của bạn
    //                 ]);
    //             }
    //         }

    //         // 3. XỬ LÝ TRẠNG THÁI PHIẾU (Logic thông minh)
    //         if ($action === 'complete') {
    //             // Nếu bấm "Hoàn tất" (F9) -> Phải kiểm tra xem đã nhập đủ chưa
    //             $pendingCount = $testRequest->testResults()->whereNull('result_value')->count();

    //             if ($pendingCount > 0) {
    //                 DB::rollBack();
    //                 return back()->withInput()->with('error', "Không thể Hoàn tất! Còn thiếu $pendingCount chỉ số chưa nhập.");
    //             }

    //             // Nếu đủ rồi -> Completed
    //             $testRequest->update(['status' => 'completed']);
    //             $message = 'Đã hoàn tất phiếu xét nghiệm!';
    //         } else {
    //             // Nếu bấm "Lưu tạm" (F8) -> Giữ nguyên processing
    //             // (Dòng này để đảm bảo nếu phiếu đang pending thì chuyển sang processing)
    //             if ($testRequest->status !== 'completed') {
    //                 $testRequest->update(['status' => 'processing']);
    //             }
    //             $message = 'Đã lưu tạm kết quả.';
    //         }

    //         DB::commit();

    //         return redirect()->route('test_results.index')->with('success', $message);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Lỗi: ' . $e->getMessage());
    //     }
    // }

    public function updateResults(Request $request, $test_request_id)
    {
        $testRequest = TestRequest::findOrFail($test_request_id);

        // Lấy dữ liệu gửi lên (Lúc này là mảng đa chiều)
        $inputs = $request->input('results', []); 
        $action = $request->input('action'); // 'save_draft' hoặc 'complete'

        // --- 1. KIỂM TRA DỮ LIỆU RỖNG (Logic mới cho mảng đa chiều) ---
        // Chúng ta phải lọc xem có ít nhất một ô 'result_value' nào được nhập không
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
                // (Dùng trim trên $val là đúng, vì $val là string)
                if ($val !== null && trim($val) !== '') {
                    $testResult = TestResult::find($resultId);

                    // Check bảo mật: Đảm bảo kết quả thuộc đúng phiếu này
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






// namespace App\Http\Controllers;

// use App\Models\TestRequest;
// use App\Models\TestResult;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
// use Picqer\Barcode\BarcodeGeneratorPNG;

// class TestResultController extends Controller
// {
    // ====================================================
    // PHẦN 1: KỸ THUẬT VIÊN - NHẬP KẾT QUẢ
    // ====================================================

    /**
     * Danh sách chờ nhập kết quả (Status: pending)
     * View: test_results/pending_entry
     */
    // public function pendingEntryList(Request $request)
    // {
    //     $query = TestRequest::with(['patient', 'doctor'])
    //                 ->where('status', 'pending')
    //                 ->latest();

    //     if ($request->has('keyword') && $request->keyword != '') {
    //         $keyword = $request->keyword;
    //         $query->where(function ($q) use ($keyword) {
    //             $q->where('request_code', 'like', "%{$keyword}%")
    //             ->orWhere('status', 'like', "%{$keyword}%")
    //             ->orWhere('created_at', 'like', "%{$keyword}%")
    //             ->orWhereHas('patient', function ($subQuery) use ($keyword) {
    //                 $subQuery->where('full_name', 'like', "%{$keyword}%")
    //                         ->orWhere('patient_code', 'like', "%{$keyword}%");
    //             })
    //             ->orWhereHas('doctor', function ($subQuery) use ($keyword) {
    //                 $subQuery->where('name', 'like', "%{$keyword}%");
    //             });
    //         });
    //     }

    //     $pendingRequests = $query->paginate(10)->appends(['keyword' => $request->keyword]);

    //     // Thay đổi: Trả về view trong thư mục mới
    //     return view('test_results.pending_entry', compact('pendingRequests'));
    // }

    /**
     * Form nhập kết quả cho 1 phiếu
     * View: test_results/enter_results
     */
    // public function enterResultForm(TestRequest $testRequest)
    // {
    //     // Kiểm tra logic: Chỉ cho phép nhập khi trạng thái hợp lệ
    //     if ($testRequest->status == 'completed') {
    //          return redirect()->route('test_results.show', $testRequest->id);
    //     }

    //     $testRequest->load(['patient', 'doctor', 'results']);
        
    //     // Thay đổi: Trả về view chuyên biệt cho việc NHẬP
    //     return view('test_results.enter_results', compact('testRequest'));
    // }

    /**
     * Xử lý Lưu kết quả (Action)
     */
    /**
     * Xử lý Lưu kết quả (Thay thế cho hàm update cũ)
     */
    // public function storeResult(Request $request, TestRequest $testRequest)
    // {
    //     // 1. Validation (Giữ nguyên logic của bạn)
    //     $validated = $request->validate([
    //         'results' => 'required|array',
    //         'results.*.result_value' => 'required|string|max:255',
    //         'results.*.notes' => 'nullable|string|max:1000',
    //     ], [
    //         'results.*.result_value.required' => 'Bạn phải nhập TẤT CẢ các ô kết quả.'
    //     ]);

    //     2. Transaction (Giữ nguyên)
    //     DB::beginTransaction();
    //     try {
    //         $allResults = $validated['results'];
    //         $now = now(); 

    //         // 3. Update từng kết quả con
    //         foreach ($allResults as $resultId => $data) {
    //             $result = TestResult::find($resultId);

    //             // Check bảo mật: Đảm bảo kết quả thuộc đúng phiếu này
    //             if ($result && $result->test_request_id == $testRequest->id) {
    //                 $result->update([
    //                     'result_value' => $data['result_value'],
    //                     'notes' => $data['notes'],
    //                     'technician_id' => Auth::id(),
    //                     'result_at' => $now,
    //                 ]);
    //             }
    //         }

    //         // 4. Update trạng thái phiếu Cha (Nếu chưa hoàn thành)
    //         if ($testRequest->status !== 'completed') {
    //             $testRequest->update([
    //                 'status' => 'processing'
    //             ]);
    //         }

    //         DB::commit();

            // ====================================================
            // 5. PHẦN SỬA ĐỔI QUAN TRỌNG: ĐIỀU HƯỚNG (REDIRECT)
            // ====================================================
            
            // Lấy trạng thái gốc từ input hidden (để biết quay về đâu)
    //         $origin = $request->input('origin_status');

    //         if ($origin == 'processing') {
    //             // Nếu sửa từ danh sách "Chờ duyệt" -> Quay về "Chờ duyệt"
    //             // Route cũ: lab.approval.pending
    //             return redirect()->route('test_results.pending_approval') 
    //                              ->with('success', 'Đã cập nhật kết quả. Phiếu nằm tại danh sách CHỜ DUYỆT.');
    //         } 
    //         elseif ($origin == 'completed') {
    //             // Nếu sửa từ "Lịch sử" -> Quay về "Lịch sử"
    //             // Route cũ: lab.approval.history
    //             return redirect()->route('test_results.approved_list')
    //                              ->with('success', 'Đã cập nhật thông tin phiếu.');
    //         } 
    //         else {
    //             // Mặc định: Quay về danh sách "Chờ nhập" để làm phiếu tiếp theo
    //             // Route cũ: lab.pending
    //             return redirect()->route('test_results.pending_entry')
    //                              ->with('success', 'Đã lưu kết quả. Phiếu chuyển sang danh sách CHỜ DUYỆT.');
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()
    //                          ->with('error', 'Lỗi hệ thống: ' . $e->getMessage())
    //                          ->withInput();
    //     }
    // }
    
    /**
     * API trả về danh sách kết quả (Dạng JSON) để AJAX gọi
     */
    // public function getResultsJson(TestRequest $testRequest)
    // {
    //     // Chỉ lấy những kết quả đã có giá trị
    //     $results = $testRequest->results()
    //                            ->whereNotNull('result_value')
    //                            ->get(['id', 'result_value', 'notes']); // Chỉ lấy cột cần thiết

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $results
    //     ]);
    // }
    
    /**
     * Tìm kiếm AJAX (Dùng chung cho cả trang Duyệt và Lịch sử)
     */
    // public function search(Request $request)
    // {
    //     $queryText = $request->input('query');
    //     $status = $request->input('status'); // 'processing' hoặc 'completed'

    //     $requests = TestRequest::with(['patient', 'doctor'])
    //         ->where('status', $status)
            
    //         ->where(function($q) use ($queryText) {
    //             $q->where('request_code', 'LIKE', "%{$queryText}%")
    //             ->orWhere('updated_at', 'LIKE', "%{$queryText}%")
    //               ->orWhereHas('patient', function($subQ) use ($queryText) {
    //                   $subQ->where('full_name', 'LIKE', "%{$queryText}%")
    //                        ->orWhere('patient_code', 'LIKE', "%{$queryText}%");
    //               })
    //               ->orWhereHas('doctor', function($subQ) use ($queryText) {
    //                   $subQ->where('name', 'LIKE', "%{$queryText}%");
    //               });
    //         })
    //         ->latest()
    //         ->paginate(5);

    //     return response()->json($requests);
    // }

    /**
     * In tem mã vạch
     * View: test_results/print_label
     */
    // public function printLabel(TestRequest $testRequest)
    // {
    //     $testRequest->load('patient');
    //     $generator = new BarcodeGeneratorPNG();
        
    //     try {
    //          $barcodePng = $generator->getBarcode($testRequest->request_code, $generator::TYPE_CODE_128, 2, 50, [0, 0, 0]);
    //          $barcodeBase64 = !empty($barcodePng) ? 'data:image/png;base64,' . base64_encode($barcodePng) : null;
    //     } catch (\Exception $e) {
    //          Log::error("Barcode Error: " . $e->getMessage());
    //          $barcodeBase64 = null;
    //     }
        
    //     // Trỏ về file view mới
    //     return view('test_results.print_label', compact('testRequest', 'barcodeBase64'));
    // }

    // ====================================================
    // PHẦN 2: BÁC SĨ/TRƯỞNG KHOA - DUYỆT KẾT QUẢ
    // ====================================================

    /**
     * Danh sách chờ duyệt (Status: processing)
     * View: test_results/pending_approval
     */
    // public function pendingApprovalList(Request $request)
    // {
    //     $query = TestRequest::with(['patient', 'doctor'])
    //         ->where('status', 'processing')
    //         ->latest();

    //     // (Bạn có thể thêm logic tìm kiếm tương tự phần pending ở đây)

    //     $requests = $query->paginate(10);
        
    //     return view('test_results.pending_approval', compact('requests'));
    // }

    /**
     * Xem chi tiết để Duyệt
     * View: test_results/approve_detail
     */
    // public function approveDetail(TestRequest $testRequest)
    // {
    //     $testRequest->load(['patient', 'doctor', 'results']);
        
    //     // Thay đổi: Trả về view chuyên biệt cho việc DUYỆT
    //     return view('test_results.approve_detail', compact('testRequest'));
    // }

    /**
     * Hành động: Xác nhận Duyệt
     */
    // public function approveAction(TestRequest $testRequest)
    // {
    //     if ($testRequest->status !== 'processing') {
    //         return back()->with('error', 'Phiếu này không ở trạng thái chờ duyệt.');
    //     }

    //     $testRequest->update([
    //         'status' => 'completed',
    //         // 'approved_at' => now(), // Uncomment nếu database có cột này
    //         // 'approver_id' => Auth::id(), // Uncomment nếu database có cột này
    //     ]);

    //     return redirect()->route('test_results.pending_approval')
    //         ->with('success', 'Đã duyệt phiếu thành công!');
    // }

    // ====================================================
    // PHẦN 3: LỊCH SỬ & IN ẤN
    // ====================================================

    /**
     * Danh sách Đã duyệt (Lịch sử)
     * View: test_results/approved_list
     */
//     public function approvedList()
//     {
//         $requests = TestRequest::with(['patient', 'doctor'])
//             ->where('status', 'completed')
//             ->latest()
//             ->paginate(10);

//         return view('test_results.approved_list', compact('requests'));
//     }

//     /**
//      * Xem lại chi tiết phiếu đã duyệt (Read-only)
//      * View: test_results/show
//      */
//     public function show(TestRequest $testRequest)
//     {
//         $testRequest->load(['patient', 'doctor', 'results']);
//         return view('test_results.show', compact('testRequest'));
//     }

// }