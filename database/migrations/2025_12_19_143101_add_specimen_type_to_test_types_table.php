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
        Schema::table('test_types', function (Blueprint $table) {
            // Thêm cột loại mẫu, cho phép null (để không lỗi dữ liệu cũ), đặt sau tên xét nghiệm
            $table->string('specimen_type')->nullable()->after('test_name')->comment('Loại mẫu: Huyết thanh, Máu toàn phần, Nước tiểu...');
        });
    }

    public function down()
    {
        Schema::table('test_types', function (Blueprint $table) {
            $table->dropColumn('specimen_type');
        });
    }
};
