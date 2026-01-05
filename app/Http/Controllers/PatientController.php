<?php

namespace App\Http\Controllers;

use App\Models\Patient;       // <-- Import model
use Illuminate\Http\Request; // <-- Import Request
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;


class PatientController extends Controller
{
    /**
     * 1. Hiển thị danh sách bệnh nhân
     */
    public function index()
    {
        $patients = Patient::latest()->paginate(10); // Lấy 10 BN mới nhất, có phân trang
        
        return view('patients.index', compact('patients'));    
    }

    /**
     * 2. Hiển thị form thêm mới
     */
    public function create()
    {
       // 1. Tính toán mã dự kiến
    $prefix = 'BN' . date('ymd'); // Ví dụ: BN251209
    
    // Tìm bệnh nhân mới nhất trong ngày
    $latest = \App\Models\Patient::where('patient_code', 'like', $prefix . '%')
                ->orderBy('patient_code', 'desc')
                ->first();

    if ($latest) {
        // Cắt bỏ 8 ký tự đầu (BN + 6 số ngày) để lấy số thứ tự
        // BN2512090005 -> lấy 0005 -> cộng 1
        $nextNumber = intval(substr($latest->patient_code, 8)) + 1;
    } else {
        $nextNumber = 1;
    }

    // Tạo mã dự kiến: BN2512090001
    $suggested_code = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

    return view('patients.create', compact('suggested_code'));
    }

    /**
     * 3. Lưu dữ liệu từ form thêm mới
     */
    public function store(Request $request)
    {
    // 1. Validate dữ liệu
    $validatedData = $request->validate([
        'full_name' => [
            'required',
            'string',
            'max:255',
            // Regex: Ít nhất 2 từ, chỉ chứa chữ và 1 dấu cách giữa các từ
            'regex:/^[\p{L}\p{M}]+(\s[\p{L}\p{M}]+)+$/u'
        ],
        'dob' => [
            'required',
            'date',
            'before_or_equal:today', 
            'after:1900-01-01' 
        ],
        'gender' => 'required|in:male,female,other',
        'phone_number' => [
            'required',
            'string',
            'regex:/^(0[0-9]{9,10})$/',
            'unique:patients,phone_number'
        ],
        'address' => 'nullable|string',
    ], [
        'full_name.regex' => 'Họ và tên không hợp lệ',
        'full_name.required' => 'Vui lòng nhập họ & tên',

        'phone_number.regex' => 'Số điện thoại phải có 10 hoặc 11 chữ số.',
        'phone_number.unique' => 'Số điện thoại đã tồn tại.',
        'phone_number.required' => 'Vui lòng nhập số điện thoại',

        'gender.required' => 'Vui lòng chọn giới tính',

        'dob.before_or_equal' => 'Ngày sinh không hợp lệ.',
        'dob.after' => 'Ngày sinh không hợp lệ.',
        'dob.required' => 'Vui lòng chọn ngày sinh',
    ]);

    try {
        // 2. Sử dụng Transaction để sinh mã và lưu an toàn
        DB::transaction(function () use ($validatedData) {
            
            // --- LOGIC SINH MÃ BỆNH NHÂN (START) ---
            $prefix = 'BN' . date('ymd'); // Ví dụ: BN251209
            
            // Tìm mã lớn nhất hôm nay và KHÓA dòng đó lại (lockForUpdate)
            // Để ngăn 2 người tạo cùng lúc bị trùng
            $latest = Patient::where('patient_code', 'like', $prefix . '%')
                        ->orderBy('patient_code', 'desc')
                        ->lockForUpdate()
                        ->first();

            if ($latest) {
                // Nếu đã có mã, lấy số thứ tự cuối + 1
                $nextNr = intval(substr($latest->patient_code, 8)) + 1;
            } else {
                // Nếu chưa có, bắt đầu từ 1
                $nextNr = 1;
            }

            // Tạo mã cuối cùng: BN2512090001
            $finalCode = $prefix . str_pad($nextNr, 4, '0', STR_PAD_LEFT);
            // --- LOGIC SINH MÃ BỆNH NHÂN (END) ---

            // 3. Gộp mã vừa sinh vào mảng dữ liệu đã validate
            $validatedData['patient_code'] = $finalCode;

            // 4. Tạo bệnh nhân
            Patient::create($validatedData);
        });

        return redirect()->route('patients.index')
                         ->with('success', 'Thêm mới bệnh nhân thành công.');

    } catch (\Exception $e) {
        // Bắt lỗi nếu có sự cố
        return back()->withInput()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
    }
    }

    /**
     * 4. Hiển thị form chỉnh sửa
     * (Laravel tự động tìm Patient dựa trên $id từ URL)
     */
    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    /**
     * 5. Cập nhật dữ liệu từ form chỉnh sửa
     */
    public function update(Request $request, Patient $patient)
    {
    $validatedData = $request->validate([
        'patient_code' => 'required|string|max:20|unique:patients,patient_code,' . $patient->id,
    
        'full_name' => [
            'required',
            'string',
            'max:255',
            'regex:/^[\p{L}\p{M}]+(\s[\p{L}\p{M}]+)+$/u'
        ],
        'dob' => [
            'required',
            'date',
            'before_or_equal:today', // Phải là ngày hôm nay hoặc trong quá khứ
            'after:1900-01-01'       // Phải sau năm 1900 (tránh ngày phi lý)
        ],
        'gender' => 'required|in:male,female,other',
        'phone_number' => [
            'required',
            'string',
            'regex:/^(0[0-9]{9,10})$/',
            'unique:patients,phone_number' . ',' . $patient->id // Bỏ qua số hiện tại
        ],
        'address' => 'nullable|string',
    ], [
        'full_name.regex' => 'Họ và tên không hợp lệ',
        'full_name.required' => 'Vui lòng nhập họ & tên',

        'phone_number.regex' => 'Số điện thoại phải có 10 hoặc 11 chữ số.',
        'phone_number.unique' => 'Số điện thoại đã tồn tại.',
        'phone_number.required' => 'Vui lòng nhập số điện thoại',

        'gender.required' => 'Vui lòng chọn giới tính',

        'dob.before_or_equal' => 'Ngày sinh không hợp lệ.',
        'dob.after' => 'Ngày sinh không hợp lệ.',
        'dob.required' => 'Vui lòng chọn ngày sinh',
    ]);

    $patient->update($validatedData);

    return redirect()->route('patients.index')
                     ->with('success', 'Cập nhật thông tin bệnh nhân thành công.');
    }

    /**
     * 6. Xóa bệnh nhân
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')
                         ->with('success', 'Xóa bệnh nhân thành công.');
    }

    /**
     * 7. Hiển thị chi tiết 
     */
    public function show(Patient $patient)
    {
        return view('patients.show', compact('patient'));
    }

    /**
     * Hàm xử lý tìm kiếm bằng AJAX
     */
    public function search(Request $request): JsonResponse
    {
    $query = $request->input('query');
    $patientQuery = Patient::query();
    // Nếu có từ khoá tìm kiếm
    if ($query) {
        $patientQuery->where(function($q) use ($query) {
            $q->where('patient_code', 'LIKE', "%{$query}%")
              ->orWhere('full_name', 'LIKE', "%{$query}%")
              ->orWhere('phone_number', 'LIKE', "%{$query}%");
            });
    }
    // Lấy 5 kết quả mới nhất có phân trang1
    $patients = $patientQuery->latest()->paginate(5);
    // Trả về dữ liệu dạng JSON
    return response()->json($patients);
    }
}