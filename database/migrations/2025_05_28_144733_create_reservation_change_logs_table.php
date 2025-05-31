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
        Schema::create('reservation_change_logs', function (Blueprint $table) {
            $table->id()->comment('Mã log');
            $table->foreignId('reservation_id')->comment('Mã đặt bàn')->constrained('reservations', 'id')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->comment('Nhân viên thay đổi')->constrained('users', 'id')->onDelete('set null');
            $table->timestamp('change_timestamp')->useCurrent()->comment('Thời điểm thay đổi');
            $table->string('change_type', 100)->comment('Loại thay đổi');
            $table->string('field_changed', 50)->nullable()->comment('Trường thay đổi');
            $table->text('old_value')->nullable()->comment('Giá trị cũ');
            $table->text('new_value')->nullable()->comment('Giá trị mới');
            $table->text('description')->nullable()->comment('Mô tả thay đổi');
            $table->comment('Lịch sử thay đổi của đặt bàn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_change_logs');
    }
};
