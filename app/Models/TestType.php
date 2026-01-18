<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestType extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     */
    protected $fillable = [
        'test_code',    // Mã XN (VD: GLU)
        'test_name',    // Tên XN (VD: Glucose)
        'category_name',// Nhóm (VD: Sinh hóa)
        'specimen_type', // Loại mẫu (VD: Máu, Nước tiểu)
        'unit',         // Đơn vị (VD: mg/dL)
        'normal_range', // Khoảng tham chiếu (VD: 70-110)
        'price',        // Giá của loại xét nghiệm (VD: 150.00) 
    ];

    // Định nghĩa danh sách loại mẫu
    const SPECIMEN_TYPES = [
        'Huyết thanh',
        'Huyết tương',
        'Máu toàn phần',
        'Nước tiểu',
        'Phân',
        'Dịch',
        'Khác'
    ];

    const CATEGORIES = [
        'Nuớc tiểu',
        'Huyết học',
        'Sinh hóa',
        'Miễn dịch & Vi sinh',
        'Chung',
    ];

    // Một loại xét nghiệm có thể có nhiều kết quả xét nghiệm
    public function testResults()
    {
        return $this->hasMany(TestResult::class, 'test_type_id');
    }
}