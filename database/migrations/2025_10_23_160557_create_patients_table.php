<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id(); // Cột ID tự tăng (khóa chính)
            
            // Mã bệnh nhân (dùng để liên kết với máy XN)
            $table->string('patient_code')->unique(); 
            
            $table->string('full_name'); // Họ và tên
            $table->date('dob'); // Ngày sinh
            $table->enum('gender', ['male', 'female', 'other']); // Giới tính
            $table->string('address')->nullable(); // Địa chỉ (có thể trống)
            $table->string('phone_number')->nullable(); // SĐT (có thể trống)
            
            // Tự động tạo 2 cột: created_at và updated_at
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};