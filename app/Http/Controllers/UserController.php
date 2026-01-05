<?php

namespace App\Http\Controllers;

use App\Models\User; // <-- Import User model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // <-- Import Rule để validate role
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng
     */
    public function index()
    {
        // Lấy tất cả user, phân trang
        $users = User::latest()->paginate(15);
        return view('users.index', compact('users'));
    }
    
    /**
     * Hiển thị form thêm người dùng mới
     */
    public function create()
    {
        // Gửi danh sách vai trò sang view để chọn
        $roles = ['admin', 'doctor', 'technician'];
        return view('users.create', compact('roles'));
    }

    /**
     * Lưu người dùng mới vào CSDL
     */
    public function store(Request $request)
    {
        // 1. Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users', // Email không được trùng
            'password' => 'required|string|min:6|confirmed', // Mật khẩu phải khớp với ô "Nhập lại mật khẩu"
            'role' => ['required', Rule::in(['admin', 'doctor', 'technician'])],
        ], [
            'name' => 'Vui lòng nhập tên người dùng',

            'email' => 'Vui lòng nhập email người dùng', 
            'email.unique' => 'Email này đã được sử dụng.',

            'password' => 'Vui lòng nhập mật khẩu', 
            'role' => 'Vui lòng chọn vai trò cho người dùng',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
        ]);

        // 2. Tạo User mới
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Mã hóa mật khẩu
            'role' => $validated['role'],
        ]);

        // 3. Quay về danh sách
        return redirect()->route('users.index')
                         ->with('success', 'Tạo tài khoản người dùng thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa vai trò
     */
    public function edit(User $user) // Laravel tự tìm User dựa vào {user} trên URL
    {
        // Danh sách các vai trò hợp lệ
        $roles = ['admin', 'doctor', 'technician'];
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Cập nhật vai trò người dùng
     */
    public function update(Request $request, User $user)
    {
        // Validate dữ liệu gửi lên
        $validated = $request->validate([
            'name' => 'required|string|max:255', // Cho phép sửa cả tên
            'role' => [
                'required',
                Rule::in(['admin', 'doctor', 'technician']), // Vai trò phải nằm trong danh sách cho phép
            ],
        ],[
            'name' => 'Vui lòng nhập tên người dùng',
            'role' => 'Vui lòng chọn vai trò cho người dùng',
        ]);

        // Cập nhật tên và vai trò
        $user->update([
            'name' => $validated['name'],
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')
                         ->with('success', 'Cập nhật thông tin người dùng thành công.');
    }
}