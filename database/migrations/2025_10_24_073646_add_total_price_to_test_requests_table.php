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
        Schema::table('test_requests', function (Blueprint $table) {
            // Thêm cột 'total_price' sau cột 'status'
            // decimal(12, 2): Cho phép tổng tiền lớn hơn
            $table->decimal('total_price', 12, 2)->after('status')->nullable(); // Có thể null ban đầu
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_requests', function (Blueprint $table) {
            $table->dropColumn('total_price');
        });
    }
};