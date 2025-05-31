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
        Schema::create('user_combo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_combo_id')->comment('Mã combo riêng')->constrained('user_combos', 'id')->onDelete('cascade');
            $table->foreignId('dish_id')->comment('Mã món ăn')->constrained('dishes', 'id')->onDelete('cascade');
            $table->integer('quantity')->default(1)->comment('Số lượng món');
            $table->comment('Chi tiết món trong combo riêng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_combo_items');
    }
};
