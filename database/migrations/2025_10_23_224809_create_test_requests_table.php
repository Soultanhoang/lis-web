<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('test_requests', function (Blueprint $table) {
        $table->id();
        // Khóa ngoại liên kết bảng 'patients'
        $table->foreignId('patient_id')->constrained('patients');
        // Khóa ngoại liên kết bảng 'users' (Bác sĩ chỉ định)
        $table->foreignId('doctor_id')->constrained('users');
        
        $table->string('request_code')->unique(); // Mã phiếu (VD: PHIEU-20251025-XXXX)
        $table->text('diagnosis')->nullable();    // Chẩn đoán của bác sĩ
        
        // Trạng thái phiếu: chờ, đã có kết quả, đã hủy
        $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_requests');
    }
};
