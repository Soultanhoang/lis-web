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
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            // Liên kết với phiếu chỉ định cha
            $table->foreignId('test_request_id')->constrained('test_requests')->onDelete('cascade');
            
            // Mã Barcode dán trên ống nghiệm (QUAN TRỌNG NHẤT)
            // Ví dụ: 251230-001-1 (Sinh hóa), 251230-001-2 (Huyết học)
            $table->string('sample_code')->unique(); 
            
            // Loại mẫu: Huyết thanh, Máu toàn phần...
            $table->string('specimen_type'); 
            
            // Trạng thái của mẫu
            $table->enum('status', ['pending', 'collected', 'received', 'rejected'])->default('pending');
            
            // Thời gian
            $table->dateTime('collected_at')->nullable(); // Lúc lấy máu
            $table->dateTime('received_at')->nullable();  // Lúc phòng xét nghiệm nhận mẫu
            
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
        Schema::dropIfExists('samples');
    }
};
