<?php

namespace App\Models;

use App\Models\Patient;
use App\Models\User;
use App\Models\TestResult;
use App\Models\Sample;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',     // ID Bệnh nhân
        'doctor_id',      // ID Bác sĩ chỉ định (người đang đăng nhập)
        'request_code',   // Mã phiếu (tự gen)
        'diagnosis',      // Chẩn đoán
        'status',         // Trạng thái (pending, completed...)
        'total_price',    // Tổng chi phí của dịch vụ xét nghiệm
    ];

    public function patient(): BelongsTo
    {
        // Một phiếu (TestRequest) THUỘC VỀ (belongsTo) một Bệnh nhân (Patient)
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Lấy thông tin bác sĩ tạo phiếu.
     */
    public function doctor(): BelongsTo
    {
        // Một phiếu THUỘC VỀ một Bác sĩ (User)
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Lấy tất cả các mẫu liên quan đến phiếu này.
     */
    public function samples()
    {
        // Một phiếu CÓ NHIỀU (hasMany) Mẫu (Sample)
        return $this->hasMany(Sample::class);
    }

    /**
     * Lấy tất cả các kết quả/chi tiết của phiếu này.
     */
    public function results(): HasMany
    {
        // Một phiếu CÓ NHIỀU (hasMany) Kết quả (TestResult)
        return $this->hasMany(TestResult::class, 'test_request_id');
        
    }

    public function testResults()
    {
        // Giả sử bảng test_results có cột test_request_id làm khóa ngoại
        return $this->hasMany(TestResult::class, 'test_request_id', 'id');
    }
}