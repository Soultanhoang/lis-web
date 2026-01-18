<?php

/**
 * Bootstrap the Laravel application
 */
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Use Laravel features
use App\Models\Patient;
use App\Models\TestRequest;
use App\Models\TestResult;
use App\Models\TestType;
use App\Models\Sample;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 

// Use the HL7 Message class
use Aranyasen\HL7\Message;

// Prevent script from timing out
set_time_limit(0);

// --- Cấu hình ---
$address = env('HL7_LISTENER_IP'); // Lấy từ .env, nếu không có thì mặc định là 0.0.0.0
$port = (int) env('HL7_LISTENER_PORT'); // Lấy từ .env, nếu không có thì mặc định là 5000
$mllp_start_char = "\x0B"; // bắt đầu MLLP
$mllp_end_segment = "\x1C\r"; // kết thúc MLLP
// ---------------------------------------------
Log::info("==========================================");
Log::info("Bắt đầu lắng nghe bản tin HL7 tại {$address}:{$port}...");

// Create, Bind, Listen (Giữ nguyên)
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($sock === false) {
    Log::error("socket_create() failed: " . socket_strerror(socket_last_error()));
    exit;
}
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1); // Cho phép tái sử dụng địa chỉ nhanh hơn
if (socket_bind($sock, $address, $port) === false) {
    Log::error("socket_bind() failed: " . socket_strerror(socket_last_error($sock)));
    socket_close($sock);
    exit;
}
if (socket_listen($sock, 5) === false) {
    Log::error("socket_listen() failed: " . socket_strerror(socket_last_error($sock)));
    socket_close($sock);
    exit;
}
Log::info("Server đang lắng nghe...");

// Loop to accept incoming connections
do {
    Log::info("Chờ kết nối...");
    $client_sock = socket_accept($sock);
    if ($client_sock === false) {
        Log::warning("socket_accept() failed: " . socket_strerror(socket_last_error($sock)));
        continue;
    }
    // Lấy IP client để log
  
    socket_getpeername($client_sock, $client_ip);
    Log::info("Client đã kết nối với IP: {$client_ip}.");

    // --- VÒNG LẶP ĐỌC DỮ LIỆU (XỬ LÝ MLLP) ---
    $full_message = ''; // Biến lưu toàn bộ dữ liệu nhận được
    $start_found = false;
    $end_found = false;

    Log::info("Đọc dữ liệu từ {$client_ip}...");
    while (true) {
        $buffer = socket_read($client_sock, 1024); // Đọc tối đa 1024 bytes

        // Lỗi đọc hoặc client ngắt kết nối
        if ($buffer === false || $buffer === '') {
            if ($buffer === false) {
                Log::warning("socket_read() failed: " . socket_strerror(socket_last_error($client_sock)));
            } else {
                Log::info("Client {$client_ip} ngắt kết nối trước khi gửi gói tin HL7.");
            }
            // Đóng kết nối phía server nếu client đóng trước khi gửi xong
             if ($client_sock) socket_close($client_sock);
             $client_sock = null; // Đánh dấu đã đóng
            break; // Thoát vòng lặp đọc
        }
        // echo "\n[DEBUG] Nhận chunk: " . addcslashes($buffer, "\r\n\t\x0B\x1C") . "\n";
        // Thêm buffer vào dữ liệu đã nhận
        $full_message .= $buffer;

        // Tìm ký tự bắt đầu (chỉ cần tìm 1 lần)
        if (!$start_found) {
            $start_pos = strpos($full_message, $mllp_start_char);
            if ($start_pos !== false) {
                // Loại bỏ dữ liệu rác trước ký tự bắt đầu
                $full_message = substr($full_message, $start_pos);
                $start_found = true;
            }
        }

        // Nếu đã tìm thấy ký tự bắt đầu, tìm ký tự kết thúc
        if ($start_found) {
            $end_pos = strpos($full_message, $mllp_end_segment);
            if ($end_pos !== false) {
                // Đã tìm thấy điểm kết thúc, cắt lấy phần tin nhắn HL7
                $hl7String = substr($full_message, strlen($mllp_start_char), $end_pos - strlen($mllp_start_char));
                $end_found = true;
                break; // Thoát vòng lặp đọc, đã có tin nhắn hoàn chỉnh
            }
        }

        // Bảo vệ: Nếu nhận quá nhiều dữ liệu mà không thấy điểm kết thúc -> lỗi
        if (strlen($full_message) > 50000) { // Giới hạn 50KB
             Log::error("Dữ liệu nhận được quá lớn từ IP: {$client_ip} mà không thấy điểm kết thúc gói tin MLLP. Đóng kết nối.");
              if ($client_sock) socket_close($client_sock);
              $client_sock = null;
             break;
        }
    } // Kết thúc while(true) đọc dữ liệu

    // Nếu vòng lặp đọc kết thúc mà chưa đóng client_sock (nghĩa là đã tìm thấy end_found hoặc lỗi khác)
     if (!$client_sock) { // Nếu client đã bị đóng do lỗi đọc hoặc ngắt kết nối sớm
          Log::warning("Bỏ qua xử lý do kết nối Client bị lỗi.");
          continue; // Bỏ qua, chờ kết nối tiếp theo
     }
    

    // --- XỬ LÝ TIN NHẮN HL7 ---
    if ($end_found && !empty($hl7String)) {
        Log::info("Đã nhận tin nhắn HL7 (" . strlen($hl7String) . " bytes) từ {$client_ip}.");
        // echo $hl7String; // Debug nếu cần xem nội dung
      
        try {
            // Normalize segment separators (quan trọng)
            $hl7String = str_replace(["\r\n", "\n"], "\r", $hl7String);

            Log::info('Phân tích chuỗi HL7...');
            $message = new Message($hl7String);
            Log::info('Phân tích bản tin HL7 thành công!');

            $sampleId = null;
            $obrSegment = $message->getFirstSegmentInstance('OBR');
            if ($obrSegment) {
                // Thường lấy từ OBR-3 (Filler Order Number) hoặc OBR-2 (Placer Order Number)
                $obr3Value = $obrSegment->getField(3); 
                if (is_object($obr3Value)) { $comp1 = $obr3Value->getComponent(1); $sampleId = $comp1 ? trim($comp1->getValue()) : null; }
                elseif (is_array($obr3Value) && isset($obr3Value[0])) { $parts = explode('^', (string)$obr3Value[0]); $sampleId = trim($parts[0] ?? null); }
                elseif (is_string($obr3Value)) { $parts = explode('^', $obr3Value); $sampleId = trim($parts[0] ?? null); }
            }

            // Kiểm tra Sample ID
            if (!$sampleId) {
                Log::error('LỖI: Không tìm thấy mã mẫu (OBR-3) trong tin nhắn HL7.');
                continue;
            }
            Log::info("  - Tìm thấy mã mẫu: " . $sampleId);

            // OBX Results
            $resultsToUpdate = [];
            $obxSegments = $message->getSegmentsByName('OBX');
            if (!empty($obxSegments)) {
                  Log::info("  - Tìm thấy " . count($obxSegments) . " phân đoạn OBX:");
                  foreach ($obxSegments as $index => $obx) {
                       // ... (logic lấy testCode, resultValue như cũ) ...
                        $testCode = null; $resultValue = null;
                        $obx3Value = $obx->getField(3); $obx5Value = $obx->getField(5);
                        if ($obx3Value) { /* ... logic lấy testCode ... */ if (is_array($obx3Value)) { $testCode = isset($obx3Value[0]) ? trim($obx3Value[0]) : null; } elseif (is_object($obx3Value)) { $comp1 = $obx3Value->getComponent(1); $testCode = $comp1 ? $comp1->getValue() : null; } elseif (is_string($obx3Value)) { $parts = explode('^', $obx3Value); $testCode = trim($parts[0] ?? null); } }
                        if ($obx5Value) { $resultValue = is_object($obx5Value) ? $obx5Value->getValue() : (string) $obx5Value; }

                        if ($testCode && $resultValue !== null) {
                            $resultsToUpdate[] = ['test_code' => trim($testCode), 'value' => trim($resultValue)];
                            Log::info("    - OBX[".($index+1)."]: Mã xét nghiệm = " . $testCode . ", Giá trị kết quả = " . $resultValue);
                        } else {
                                Log::warning("    - OBX[".($index+1)."]: Bỏ qua - thiếu mã xét nghiệm hoặc giá trị kết quả.");
                        }
                  }
            } else {
                 Log::warning("  - Không tìm thấy phân đoạn OBX nào trong bản tin.");
                 continue; // Không có kết quả để xử lý
            }
            
            // Cập nhật cơ sở dữ liệu dựa trên Sample ID và kết quả trích xuất
                if (!empty($resultsToUpdate)) {
                Log::info("Thực hiện cập nhật CSDL cho mã mẫu: {$sampleId}...");
                DB::beginTransaction();
                try {
                    // --- 1. TÌM MẪU (SAMPLE) DỰA VÀO BARCODE MÁY GỬI ---
                    $sample = Sample::with('testRequest')
                                    ->where('sample_code', $sampleId)
                                    ->first();
                    
                    $testRequest = null;

                    if ($sample) {
                        Log::info("  - Tìm thấy mẫu trong hệ thống: " . $sample->id . " (Loại mẫu: " . $sample->specimen_type . ")");
                        $testRequest = $sample->testRequest;
                        
                        // Cập nhật trạng thái mẫu: Đã nhận kết quả
                        $sample->update(['status' => 'received']); // Hoặc 'analyzed' nếu bạn muốn thêm trạng thái này
                    } else {
                        // FALLBACK: Nếu không tìm thấy mẫu, thử tìm trực tiếp bằng Mã phiếu (Hỗ trợ quy trình cũ)
                        Log::warning("  - Không tìm thấy mẫu nào khớp với mã mẫu đang chạy. Thử tìm theo mã phiếu chỉ định...");
                        $testRequest = TestRequest::where('request_code', $sampleId)->first();
                    }

                    // --- 2. KIỂM TRA PHIẾU ---
                    if (!$testRequest) {
                        Log::error("  - LỖI: Không tìm thấy phiếu chỉ định nào khớp với mã mẫu đang chạy: " . $sampleId);
                        DB::rollBack();
                        continue; 
                    }

                    // Quan trọng: Chỉ nhận kết quả nếu phiếu chưa hoàn tất (cho phép pending hoặc processing)
                    if ($testRequest->status === 'completed' && 'pending') {
                         Log::warning("  - THÔNG BÁO: Phiếu chỉ định {$testRequest->id} đã hoàn thành. Bỏ qua cập nhật.");
                         DB::rollBack();
                         continue;
                    }

                    Log::info("  - Đang xử lý kết quả cho phiếu chỉ định: " . $testRequest->id);
                    $now = now();
                    $updateCount = 0;

                    // --- 3. CẬP NHẬT KẾT QUẢ ---
                    foreach ($resultsToUpdate as $hl7Result) {
                        $testCode = $hl7Result['test_code'];
                        $value    = $hl7Result['value'];

                        // Query để tìm dòng kết quả cần update
                        $query = TestResult::where('test_request_id', $testRequest->id)
                                            ->whereHas('testType', function ($q) use ($testCode) {
                                                $q->where('test_code', $testCode); // Khớp mã xét nghiệm
                                            })
                                            ->whereNull('result_value'); // Chỉ điền vào ô trống (tránh ghi đè nếu đã nhập tay - tuỳ logic của bạn)

                        // Nếu tìm thấy Sample, hãy khớp thêm sample_id để chính xác tuyệt đối
                        if ($sample) {
                            $query->where('sample_id', $sample->id);
                        }

                        $testResult = $query->first();

                        if ($testResult) {
                            $testResult->update([
                                'result_value'  => $value,
                                'technician_id' => null, // Máy làm tự động
                                'result_at'     => $now,
                                'note'          => 'Auto-Analyzer'
                            ]);
                            $updateCount++;
                            Log::info("    -> Đã cập nhật: {$testCode} => {$value}");
                        } else {
                            Log::warning("    -> Bỏ qua: {$testCode} (Không tìm thấy chỉ định chờ kết quả cho mã xét nghiệm này)");
                        }
                    }

                    // --- 4. CẬP NHẬT TRẠNG THÁI PHIẾU (HOÀN TẤT HAY CHƯA?) ---
                    if ($updateCount > 0) {
                        // Đếm xem phiếu này còn bao nhiêu chỉ số chưa có kết quả
                        $pendingResults = TestResult::where('test_request_id', $testRequest->id)
                                                    ->whereNull('result_value')
                                                    ->count();
                        if ($pendingResults == 0) {
                            // Nếu đã đủ hết -> COMPLETED
                            $testRequest->update(['status' => 'completed']);
                            Log::info("  - Trạng thái phiếu chỉ định: {$testRequest->id} được cập nhật sang đã có kết quả.");
                        } else {
                            // Nếu chưa đủ -> Đảm bảo là PROCESSING (để thoát khỏi danh sách pending)
                            if ($testRequest->status !== 'processing') {
                                $testRequest->update(['status' => 'processing']);
                                Log::info("  - Trạng thái phiếu chỉ định: {$testRequest->id} được cập nhật sang đang xử lý.");
                            }
                        }
                    }   
                    // --- 4. CẬP NHẬT TRẠNG THÁI (Backup cho trường hợp quên xác nhận lấy mẫu) ---
                    // if ($updateCount > 0) {
                    //     // Chỉ can thiệp khi phiếu vẫn đang treo ở 'pending'
                    //     if ($testRequest->status === 'pending') {
                    //         $testRequest->update(['status' => 'processing']);
                    //         Log::info("  - Trạng thái phiếu chỉ định: {$testRequest->id} được cập nhật sang đang xử lý.");
                    //         Log::warning("Phiếu {$testRequest->id} chưa xác nhận lấy mẫu nhưng đã có kết quả máy -> Tự động chuyển Processing.");
                    //     }
                    // }

                    DB::commit();
                    Log::info("  - HOÀN TẤT CẬP NHẬT. Đã lưu {$updateCount} kết quả.");

                } catch (\Exception $dbExc) {
                    DB::rollBack();
                    Log::error("! LỖI: Không cập nhật được CSDL cho mã mẫu đang chạy | Chi tiết: " . $dbExc->getMessage());
                }
            } else {
                 Log::warning("  - Không có kết quả nào hợp lệ được trích xuất từ phân đoạn OBX.");
            }

        } catch (\Exception $e) {
             // Gửi NACK? (Nâng cao)
             // 1. Log ra màn hình console
            Log::error("! LỖI: Không phân tích được bản tin HL7 từ IP: {$client_ip} | Chi tiết: " . $e->getMessage());

            // 2. Ghi vào file log (Dùng đúng biến $full_message hoặc $hl7String)
            // Kiểm tra xem biến nào đang có dữ liệu thì dùng biến đó
            $rawData = isset($full_message) ? $full_message : (isset($hl7String) ? $hl7String : 'No Data');
            
            try {
                file_put_contents(storage_path('logs/hl7_parse_errors.log'), 
                    "[" . date('Y-m-d H:i:s') . "] From: {$client_ip}\nError: " . $e->getMessage() . "\nRaw Data:\n" . $rawData . "\n----------------\n", 
                    FILE_APPEND
                );
            } catch (\Exception $fileEx) {
                // Bỏ qua lỗi ghi file để tránh sập server
                Log::warning("Không thể ghi file log lỗi."); 
            }
        }

    } elseif ($end_found && empty($hl7String)) {
        Log::warning("Nhận được gói tin MLLP nhưng nội dung bản tin HL7 trống từ IP: {$client_ip}.");
    } else {
        // Trường hợp này xảy ra nếu client đóng kết nối trước khi gửi xong MLLP end segment
        Log::warning("Gói tin MLLP không hoàn chỉnh hoặc kết nối bị ngắt bởi IP: {$client_ip}.");
    }

    // Đóng kết nối client nếu nó chưa bị đóng do lỗi đọc
    if ($client_sock) {
         socket_close($client_sock);
         Log::info("Đóng kết nối với Client {$client_ip}.");
    }
    echo "\n"; // Thêm dòng trống cho dễ đọc log console

} while (true);

socket_close($sock);
Log::info("Server đã ngắt."); // Dòng này thường không đạt tới

?>