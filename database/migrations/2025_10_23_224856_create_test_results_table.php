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
        Schema::create('test_results', function (Blueprint $table) {
        $table->id();
        // Khóa ngoại liên kết bảng 'test_requests'
        $table->foreignId('test_request_id')->constrained('test_requests')->onDelete('cascade');
        // Khóa ngoại liên kết bảng 'test_types'
        $table->foreignId('test_type_id')->constrained('test_types');

        $table->string('result_value')->nullable()->comment('Giá trị kết quả máy trả về (NULL khi mới chỉ định)');
        $table->text('notes')->nullable()->comment('Ghi chú của KTV');
        
        // Khóa ngoại (Kỹ thuật viên duyệt) liên kết bảng 'users'
        $table->foreignId('technician_id')->nullable()->constrained('users'); 
        
        $table->timestamp('result_at')->nullable()->comment('Thời gian có kết quả');
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
        Schema::dropIfExists('test_results');
    }
};
