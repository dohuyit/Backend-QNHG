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
        Schema::create('order_item_change_logs', function (Blueprint $table) {
            $table->id()->comment('Mã log');
            $table->foreignId('order_item_id')->comment('Mã chi tiết đơn hàng')->constrained('order_items', 'id')->onDelete('cascade');
            $table->foreignId('order_id')->comment('Mã đơn hàng')->constrained('orders', 'id')->onDelete('cascade');
            $table->foreignId('user_id')->comment('Nhân viên thay đổi')->constrained('users', 'id')->onDelete('restrict');
            $table->timestamp('change_timestamp')->useCurrent()->comment('Thời điểm thay đổi');
            $table->string('change_type', 50)->comment('Loại thay đổi');
            $table->string('field_changed', 50)->nullable()->comment('Trường thay đổi');
            $table->text('old_value')->nullable()->comment('Giá trị cũ');
            $table->text('new_value')->nullable()->comment('Giá trị mới');
            $table->text('reason')->nullable()->comment('Lý do');
            $table->comment('Lịch sử thay đổi của từng món trong đơn hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_change_logs');
    }
};
