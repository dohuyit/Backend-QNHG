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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id()->comment('ID liên kết người dùng, vai trò và chi nhánh');
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('restrict');
            $table->foreignId('role_id')->constrained('roles', 'id')->onDelete('restrict');
            $table->timestamps();
            $table->unique(['user_id', 'role_id'], 'uq_user_role_branch'); // Đảm bảo một user không có cùng role ở cùng branch 2 lần
            $table->comment('Liên kết người dùng với vai trò tại một chi nhánh cụ thể');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
