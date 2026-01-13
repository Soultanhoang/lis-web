<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_code',
        'full_name',
        'dob',
        'gender',
        'address',
        'phone_number',
    ];

    // Một bệnh nhân có thể có nhiều phiếu chỉ định xét nghiệm
    public function testRequests()  
    {
        return $this->hasMany(TestRequest::class, 'patient_id');
    }   
}