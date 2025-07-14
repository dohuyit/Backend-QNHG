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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id()->comment('Mã chi tiết giỏ hàng');
            $table->foreignId('cart_id')->comment('Mã giỏ hàng')->constrained('carts', 'id')->onDelete('cascade');
            $table->foreignId('dish_id')->nullable()->comment('Mã món ăn')->constrained('dishes', 'id')->onDelete('set null');
            $table->foreignId('combo_id')->nullable()->comment('Mã combo')->constrained('combos', 'id')->onDelete('set null');
            $table->integer('quantity')->default(1)->comment('Số lượng');
            $table->decimal('price', 10, 2)->comment('Giá tại thời điểm thêm vào giỏ');
            $table->timestamps();
            $table->comment('Chi tiết món ăn/combo trong giỏ hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
