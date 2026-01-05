<?php

namespace App\Models;

// THÊM 3 DÒNG NÀY VÀO ĐẦU FILE:
use App\Models\TestType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt.
     */
    protected $fillable = [
        'test_request_id',
        'sample_id',
        'test_type_id',
        'result_value',
        'notes',
        'technician_id',
        'result_at',
    ];
    protected $casts = [
        'result_at' => 'datetime', // Tự động ép kiểu sang Carbon Object
    ];
    /**
     * Lấy thông tin mẫu liên quan đến kết quả này.
     */
    public function sample()
    {
        // Một kết quả (TestResult) THUỘC VỀ (belongsTo) một Mẫu (Sample)
        return $this->belongsTo(Sample::class);
    }

    /**
     * Lấy thông tin loại xét nghiệm của kết quả này.
     */
    public function testType(): BelongsTo
    {
        // Một kết quả (TestResult) THUỘC VỀ (belongsTo) một Loại XN (TestType)
        return $this->belongsTo(TestType::class, 'test_type_id');
    }
}