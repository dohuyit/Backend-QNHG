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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id()->comment('ID liên kết quyền và vai trò');
            $table->foreignId('role_id')->comment('Mã vai trò')->constrained('roles', 'id')->onDelete('restrict');
            $table->foreignId('permission_id')->comment('Mã quyền hạn')->constrained('permissions', 'id')->onDelete('restrict');
            $table->timestamps();
            $table->unique(['role_id', 'permission_id'], 'uq_role_permission');
            $table->comment('Liên kết giữa vai trò và các quyền hạn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
