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
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id()->comment('Mã danh mục bài viết');
            $table->string('category_name', 100)->unique()->comment('Tên danh mục');
            $table->text('description')->nullable()->comment('Mô tả');
            $table->boolean('is_visible')->default(true)->comment('Hiển thị');
            $table->timestamps();
            $table->comment('Danh mục bài viết website/blog');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_categories');
    }
};
