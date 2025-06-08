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
        Schema::create('table_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->notNull();
            $table->text('description')->nullable()->comment('Mô tả chi tiết');
            $table->string('status')->default('active')->comment('Trạng thái khu vực bàn');
            $table->integer('capacity')->default(0)->comment('Số lượng bàn thực tế trong khu vực');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');

            $table->comment('Các khu vực bàn trong mỗi chi nhánh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_areas');
        Schema::table('table_areas', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
