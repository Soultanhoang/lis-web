<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('test_types', function (Blueprint $table) {
            // 1. Xóa cột string cũ đi (Dữ liệu cột này sẽ mất, nhưng không sao vì ta cần nhập lại cho chuẩn)
            $table->dropColumn('specimen_type');
        });

        Schema::table('test_types', function (Blueprint $table) {
            // 2. Tạo lại cột mới dạng ENUM
            $table->enum('specimen_type', [
                'Huyết thanh',
                'Huyết tương',
                'Máu toàn phần',
                'Nước tiểu',
                'Phân',
                'Dịch',
                'Khác'
            ])->nullable()->after('test_name')->comment('Loại mẫu bệnh phẩm');
        });
    }

    public function down()
    {
        // Khi rollback (quay lui) thì làm ngược lại
        Schema::table('test_types', function (Blueprint $table) {
            $table->dropColumn('specimen_type');
        });

        Schema::table('test_types', function (Blueprint $table) {
            $table->string('specimen_type')->nullable()->after('test_name');
        });
    }
};
