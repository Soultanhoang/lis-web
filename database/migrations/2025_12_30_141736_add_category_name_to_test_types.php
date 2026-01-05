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
            // Mặc định là 'Chung' nếu chưa phân loại
            $table->string('category_name')->default('Chung')->after('test_name')->comment('Nhóm xét nghiệm: Sinh hóa, Huyết học...');
        });
    }

    public function down()
    {
        Schema::table('test_types', function (Blueprint $table) {
            $table->dropColumn('category_name');
        });
    }
};
