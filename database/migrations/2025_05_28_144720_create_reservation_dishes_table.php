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
        Schema::create('reservation_dishes', function (Blueprint $table) {
            $table->id()->comment('Mã món ăn đặt trước');
            $table->foreignId('reservation_id')->comment('Mã đặt bàn')->constrained('reservations', 'id')->onDelete('cascade');
            $table->foreignId('dish_id')->comment('Mã món ăn')->constrained('dishes', 'id')->onDelete('cascade');
            $table->integer('quantity')->default(1)->comment('Số lượng');
            $table->text('note')->nullable()->comment('Ghi chú');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Các món ăn đặt trước cùng đặt bàn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_dishes');
    }
};
