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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id()->comment('Mã quyền hạn');
            $table->string('permission_name', 100)->unique()->comment('Tên định danh quyền');
            $table->string('slug', 120)->unique()->nullable()->comment('Slug cho quyền hạn');
            $table->foreignId('permission_group_id')->comment('Mã nhóm quyền')->constrained('permission_groups', 'id');
            $table->text('description')->nullable()->comment('Mô tả quyền hạn');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Danh sách các quyền hạn chi tiết');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
