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
        Schema::create('admin_faces', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unique();
            $table->string('email');
            $table->string('full_name');
            $table->enum('role_name', ['admin', 'bếp', 'nhân viên'])->default('nhân viên');
            $table->text('face_encoding')->nullable(); // Lưu vector encoding của khuôn mặt
            $table->boolean('is_trained')->default(false); // Đã training chưa
            $table->timestamps();
            
            $table->index(['user_id', 'is_trained']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_faces');
    }
};
