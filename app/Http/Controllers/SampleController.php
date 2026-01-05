<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestRequest;
use App\Models\Sample;
use Illuminate\Support\Facades\DB;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Facades\Log;

class SampleController extends Controller
{
    /**
     * 1. Danh sách chờ lấy mẫu
     */
    public function index(Request $request)
    {
        // Query các phiếu có mẫu đang chờ (status = pending)
        $query = TestRequest::with(['patient', 'doctor'])
                    ->where('status', 'pending')             
                    ->latest();

        // Tìm kiếm
        if ($request->has('keyword') && $request->keyword != '') {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('request_code', 'like', "%{$keyword}%")
                ->orWhereHas('samples', function ($subQuery) use ($keyword) {
                    $subQuery->where('sample_code', 'like', "%{$keyword}%");
                })
                ->orWhereHas('patient', function ($subQuery) use ($keyword) {
                    $subQuery->where('full_name', 'like', "%{$keyword}%")
                             ->orWhere('patient_code', 'like', "%{$keyword}%");
                });
            });
        }

        $pendingRequests = $query->paginate(10)->appends(['keyword' => $request->keyword]);

        return view('samples.index', compact('pendingRequests'));
    }

    /**
     * 2. Trang giao diện Lấy mẫu (Form Create)
     */
   public function create($test_request_id)
    {
        // 1. Lấy thông tin phiếu
        $testRequest = TestRequest::with(['patient', 'doctor'])->findOrFail($test_request_id);

        // 2. [LOGIC MỚI] KIỂM TRA & TỰ ĐỘNG SINH MẪU (Cho các phiếu cũ)
        // Nếu phiếu này chưa có dòng nào trong bảng samples
        if ($testRequest->samples()->count() == 0) {
            
            // Lấy danh sách các xét nghiệm của phiếu này để biết cần sinh mẫu gì
            // Giả sử bạn có bảng trung gian test_request_items hoặc bảng kết quả test_results
            // Ở đây tôi query từ bảng test_results (vì bạn đã liên kết ở các bước trước)
            // Hoặc query từ bảng test_requests_items nếu bạn dùng bảng đó.
            
            // Cách đơn giản nhất: Lấy từ các TestResult đã tạo (như logic Hybrid mình bàn)
            $results = \App\Models\TestResult::with('testType')
                            ->where('test_request_id', $testRequest->id)
                            ->get();
            
            if ($results->isEmpty()) {
                 return redirect()->route('samples.index')
                                  ->with('error', 'Phiếu này chưa có chỉ định xét nghiệm nào.');
            }

            // Gom nhóm theo loại mẫu
            $groups = $results->groupBy(function($item) {
                return $item->testType->specimen_type ?? 'Khác';
            });

            $sampleCounter = 1;
            
            // Tạo mẫu bổ sung
            foreach ($groups as $type => $items) {
                $sampleCode = $testRequest->request_code . '-' . $sampleCounter;
                
                $sample = Sample::create([
                    'test_request_id' => $testRequest->id,
                    'sample_code'     => $sampleCode,
                    'specimen_type'   => $type,
                    'status'          => 'pending',
                ]);

                // Cập nhật ngược lại sample_id cho các kết quả (quan trọng)
                foreach ($items as $result) {
                    $result->update(['sample_id' => $sample->id]);
                }
                
                $sampleCounter++;
            }
            
            // Refresh lại quan hệ để lấy dữ liệu mới vừa tạo
            $testRequest->load('samples');
        }

        // 3. Lọc lại chỉ lấy mẫu pending để hiển thị ra form (nếu muốn)
        // Hoặc lấy all() nếu muốn hiện cả mẫu đã lấy
        // Ở đây ta load lại quan hệ samples
        $testRequest->load(['samples' => function($q) {
            // $q->where('status', 'pending'); // Tạm bỏ comment dòng này nếu muốn hiện hết để debug
        }]);

        // Kiểm tra lần cuối, nếu vẫn trống thì mới đẩy về
        if ($testRequest->samples->isEmpty()) {
             return redirect()->route('samples.index')
                              ->with('warning', 'Hệ thống không xác định được loại mẫu cho phiếu này.');
        }

        return view('samples.create', compact('testRequest'));
    }
    /**
     * 3. Xử lý Lưu xác nhận lấy mẫu (Store Action)
     */
   public function store(Request $request)
    {
        $request->validate([
            'sample_ids' => 'required|array',
            'sample_ids.*' => 'exists:samples,id',
        ], [
            'sample_ids.required' => 'Bạn chưa chọn mẫu nào.',
        ]);

        DB::beginTransaction();
        try {
            // 1. Cập nhật trạng thái các MẪU được chọn -> 'received'
            // (Nghĩa là phòng xét nghiệm đã nhận được mẫu này)
            Sample::whereIn('id', $request->sample_ids)->update([
                'status' => 'received',
                'collected_at' => now(), // Thời điểm lấy
                'received_at' => now(),  // Thời điểm nhận
            ]);

            // --- LOGIC QUAN TRỌNG: CẬP NHẬT TRẠNG THÁI PHIẾU ---

            // 2. Lấy ID của phiếu chỉ định từ mẫu đầu tiên trong danh sách gửi lên
            $firstSampleId = $request->sample_ids[0];
            $testRequestId = Sample::find($firstSampleId)->test_request_id;
            
            // 3. Tìm phiếu đó
            $testRequest = TestRequest::find($testRequestId);

            // 4. Kiểm tra xem phiếu này CÒN mẫu nào chưa lấy (pending) không?
            $remainingSamples = $testRequest->samples()->where('status', 'pending')->count();

            // 5. Nếu còn 0 mẫu pending (Tức là đã lấy hết sạch mẫu)
            if ($remainingSamples == 0) {
                // Chuyển trạng thái phiếu sang 'processing' (Đang xử lý)
                // Để nó thoát khỏi danh sách "Chờ lấy mẫu"
                $testRequest->update([
                    'status' => 'processing'
                ]);
            }

            DB::commit();

            return redirect()->route('samples.index')
                             ->with('success', 'Đã xác nhận lấy mẫu thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * In tem mã vạch
     */
    public function printLabel(Sample $sample)
    {
        // Load thêm thông tin bệnh nhân qua quan hệ: Sample -> TestRequest -> Patient
        // Giả sử trong model Sample bạn có function testRequest() { return $this->belongsTo(TestRequest::class); }
        $sample->load('testRequest.patient'); 

        $generator = new BarcodeGeneratorPNG();
        
        try {
            // SỬA: Tạo barcode từ sample_code (VD: 251230-1)
            $barcodePng = $generator->getBarcode($sample->sample_code, $generator::TYPE_CODE_128, 2, 50, [0, 0, 0]);
            $barcodeBase64 = !empty($barcodePng) ? 'data:image/png;base64,' . base64_encode($barcodePng) : null;
        } catch (\Exception $e) {
            Log::error("Barcode Error: " . $e->getMessage());
            $barcodeBase64 = null;
        }
        
        // Truyền biến $sample sang view
        return view('samples.print_label', compact('sample', 'barcodeBase64'));
    }
}