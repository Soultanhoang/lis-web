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
        Schema::create('test_types', function (Blueprint $table) {
        $table->id();
        $table->string('test_code')->unique()->comment('Mã xét nghiệm (ví dụ: GLU)');
        $table->string('test_name')->comment('Tên xét nghiệm (ví dụ: Glucose)');
        $table->string('unit')->nullable()->comment('Đơn vị (ví dụ: mg/dL)');
        $table->string('normal_range')->nullable()->comment('Khoảng tham chiếu');
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
        Schema::dropIfExists('test_types');
    }
};
