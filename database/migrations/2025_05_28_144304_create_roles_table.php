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
        Schema::create('roles', function (Blueprint $table) {
            $table->id()->comment('Mã vai trò');
            $table->string('role_name', 50)->unique()->comment('Tên vai trò');
            $table->string('slug', 60)->unique()->nullable()->comment('Slug cho vai trò');
            $table->text('description')->nullable()->comment('Mô tả vai trò');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Các vai trò của người dùng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
