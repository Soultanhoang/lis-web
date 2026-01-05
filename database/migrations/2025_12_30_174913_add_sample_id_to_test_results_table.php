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
        Schema::table('test_results', function (Blueprint $table) {
            // Cho phép NULL vì có thể lúc đầu chưa chia mẫu xong, hoặc nhập liệu bổ sung
            $table->foreignId('sample_id')
                ->nullable()
                ->after('test_request_id')
                ->constrained('samples')
                ->onDelete('set null'); // Nếu xóa mẫu thì kết quả vẫn còn (nhưng mất liên kết)
        });
    }

    public function down()
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropForeign(['sample_id']);
            $table->dropColumn('sample_id');
        });
    }
};
