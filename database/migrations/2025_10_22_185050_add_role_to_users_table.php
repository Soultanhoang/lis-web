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
   public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Thêm cột 'role' sau cột 'email' (hoặc bất cứ đâu)
        // Giả sử có 3 vai trò: admin, doctor (bác sĩ), technician (KTV)
        // Đặt 'doctor' làm mặc định khi đăng ký
        $table->string('role')->after('email')->default('doctor');
    });
}

// (Hàm down() để rollback)
public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}
};
