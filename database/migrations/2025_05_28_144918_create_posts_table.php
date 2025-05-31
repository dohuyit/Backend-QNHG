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
        Schema::create('posts', function (Blueprint $table) {
            $table->id()->comment('Mã bài viết');
            $table->string('title', 255)->comment('Tiêu đề');
            $table->string('slug', 255)->unique()->comment('Slug SEO');
            $table->text('content')->comment('Nội dung');
            $table->string('thumbnail_url', 255)->nullable()->comment('Ảnh đại diện');
            $table->json('tags')->nullable()->comment('Các tag cho bài viết (VD: ["tin tức", "khuyến mãi", "ẩm thực"])');
            $table->foreignId('post_category_id')->nullable()->comment('Danh mục')->constrained('post_categories', 'id')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->comment('Tác giả (Users)')->constrained('users', 'id')->onDelete('set null');
            $table->boolean('is_published')->default(true)->comment('Trạng thái xuất bản');
            $table->timestamps();
            $table->comment('Các bài viết trên website/blog');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
