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
        Schema::create('kitchen_orders', function (Blueprint $table) {
            $table->id()->comment('Mã phiếu bếp');
            $table->foreignId('order_item_id')->unique()->comment('Mã chi tiết món cần làm')->constrained('order_items', 'id')->onDelete('cascade');
            $table->foreignId('order_id')->comment('Mã đơn hàng gốc')->constrained('orders', 'id')->onDelete('cascade');
            $table->string('table_number', 20)->nullable()->comment('Số bàn');
            $table->string('item_name', 255)->comment('Tên món');
            $table->integer('quantity')->comment('Số lượng');
            $table->text('notes')->nullable()->comment('Ghi chú món');
            $table->enum('status', ['pending', 'preparing', 'ready', 'cancelled'])->default('pending')->comment('Trạng thái ở bếp');
            $table->boolean('is_priority')->default(false)->comment('Ưu tiên');
            $table->timestamp('received_at')->useCurrent()->comment('TG bếp nhận');
            $table->timestamp('completed_at')->nullable()->comment('TG bếp hoàn thành');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Yêu cầu chế biến món ăn gửi xuống bếp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_orders');
    }
};
