<?php

namespace App\Models;

use App\Models\TestRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sample extends Model
{
    protected $fillable = ['test_request_id', 'sample_code', 'specimen_type', 'status', 'collected_at', 'received_at'];

    public function testRequest()
    {
        // Một mẫu (Sample) THUỘC VỀ (belongsTo) một Phiếu chỉ định (TestRequest)
        return $this->belongsTo(TestRequest::class);
    }

    public function results()
    {
        // Một mẫu CÓ NHIỀU (hasMany) Kết quả (TestResult)
        return $this->hasMany(TestResult::class);
    }
}