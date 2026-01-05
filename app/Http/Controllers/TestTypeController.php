<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;

use App\Models\TestType;      // <-- Import model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestTypeController extends Controller
{
    // 1. Hiển thị danh sách
    public function index()
    {
        $testTypes = TestType::latest()->paginate(10);
        return view('test_types.index', compact('testTypes'));
    }

    // 2. Hiển thị form thêm mới
    public function create()
    {
        return view('test_types.create');
    }

    // 3. Lưu dữ liệu thêm mới
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'test_code' => 'required|string|unique:test_types|max:20',
            'test_name' => 'required|string|max:255',
            'category_name' => 'required|string|max:100',
            'specimen_type' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'normal_range' => 'required|string|max:50',
            'price' => 'nullable|numeric|min:0',
        ],[
            'test_code.required' => 'Vui lòng nhập mã xét nghiệm',
            'test_code.unique' => 'Mã xét nghiệm này đã tồn tại',
            'test_name.required' => 'Vui lòng nhập tên xét nghiệm',
            'category_name.required' => 'Vui lòng nhập nhóm xét nghiệm',
            'specimen_type.required' => 'Vui lòng chọn loại mẫu',
            'unit.required' => 'Vui lòng nhập đơn vị',
            'normal_range.required' => 'Vui lòng nhập khoảng tham chiếu',
            'price' => 'nullable|numeric|min:0',
        ]
    );

        TestType::create($validatedData);

        return redirect()->route('test_types.index')
                         ->with('success', 'Thêm danh mục xét nghiệm thành công.');
    }

    // 4. Hiển thị form chỉnh sửa
    public function edit(TestType $testType)
    {
        return view('test_types.edit', compact('testType'));
    }

    // 5. Cập nhật dữ liệu
    public function update(Request $request, TestType $testType)
    {
        $validatedData = $request->validate([
            'test_code' => 'required|string|max:20|unique:test_types,test_code,' . $testType->id,
            'test_name' => 'required|string|max:255',
            'category_name' => 'required|string|max:100', 
            'specimen_type' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'normal_range' => 'required|string|max:50',
            'price' => 'nullable|numeric|min:0',
        ],[
            'test_code.required' => 'Vui lòng nhập mã xét nghiệm',
            'test_code.unique' => 'Mã xét nghiệm này đã tồn tại',
            'test_name.required' => 'Vui lòng nhập tên xét nghiệm',
            'category_name.required' => 'Vui lòng nhập nhóm xét nghiệm',
            'specimen_type.required' => 'Vui lòng chọn loại mẫu',
            'unit.required' => 'Vui lòng nhập đơn vị',
            'normal_range.required' => 'Vui lòng nhập khoảng tham chiếu',
            'price' => 'nullable|numeric|min:0',
        ]);

        $testType->update($validatedData);

        return redirect()->route('test_types.index')
                         ->with('success', 'Cập nhật danh mục xét nghiệm thành công.');
    }

    // 6. Xóa
    public function destroy(TestType $testType)
    {
        $testType->delete();
        return redirect()->route('test_types.index')
                         ->with('success', 'Xóa danh mục xét nghiệm thành công.');
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');
        
        $testTypeQuery = TestType::query();

        if ($query) {
            $testTypeQuery->where(function($q) use ($query) {
                // Tìm theo Mã XN, Tên XN, hoặc Giá
                $q->where('test_code', 'LIKE', "%{$query}%")
                  ->orWhere('test_name', 'LIKE', "%{$query}%")
                  ->orWhere('category_name', 'LIKE', "%{$query}%")
                  ->orWhere('specimen_type', 'LIKE', "%{$query}%")
                  ->orWhere('unit', 'LIKE', "%{$query}%")
                  ->orWhere('price', 'LIKE', "%{$query}%");
            });
        }

        // Cũng phân trang 5 kết quả
        $testTypes = $testTypeQuery->latest()->paginate(10);

        return response()->json($testTypes);
    }

    public function getByCategory(Request $request)
    {
        // 1. Lấy tên nhóm từ AJAX gửi lên (VD: "Sinh hóa")
        $categoryName = $request->input('category');

        // 2. Query dữ liệu
        $tests = TestType::where('category_name', $categoryName)
                    ->get(['id', 'test_code', 'test_name', 'category_name', 'price']); // Chỉ lấy cột cần thiết

        // 3. Trả về JSON đúng định dạng mà JS đang đợi
        return response()->json([
            'status' => 'success',
            'data' => $tests
        ]);
    }
}