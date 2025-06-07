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
            $table->id()->comment('Mã khu vực bàn');
            $table->foreignId('branch_id')->comment('Mã cơ sở/chi nhánh')->constrained('branches', 'id');
            $table->foreignId('area_template_id')->nullable()->comment('Mã mẫu khu vực')->constrained('area_templates', 'id');
            $table->string('name', 100)->comment('Tên khu vực trong chi nhánh');
            $table->string('slug', 120)->nullable()->comment('Slug (nếu cần, thường không cần cho table area)');
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
