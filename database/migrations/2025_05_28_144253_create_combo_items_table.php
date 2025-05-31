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
        Schema::create('combo_items', function (Blueprint $table) {
            $table->id()->comment('ID món trong combo');
            $table->foreignId('combo_id')->comment('Mã combo')->constrained('combos', 'id')->onDelete('cascade');
            $table->foreignId('dish_id')->comment('Mã món ăn')->constrained('dishes', 'id')->onDelete('cascade');
            $table->integer('quantity')->default(1)->comment('Số lượng món');
            $table->timestamps();
            $table->unique(['combo_id', 'dish_id'], 'uq_combo_dish');
            $table->comment('Chi tiết các món ăn trong một combo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_items');
    }
};
