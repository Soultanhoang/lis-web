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
        Schema::table('test_types', function (Blueprint $table) {
            // Thêm cột 'price' sau cột 'normal_range'
            // decimal(10, 2): Tổng cộng 10 chữ số, 2 chữ số sau dấu phẩy (VD: 12345678.99)
            // default(0): Giá mặc định là 0
            $table->decimal('price', 10, 2)->after('normal_range')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_types', function (Blueprint $table) {
            $table->dropColumn('price'); // Xóa cột nếu rollback
        });
    }
};