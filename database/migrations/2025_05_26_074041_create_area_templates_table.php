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
        Schema::create('area_templates', function (Blueprint $table) {
            $table->id()->comment('Mã mẫu khu vực');
            $table->string('name', 100)->comment('Tên mẫu khu vực');
            $table->text('description')->nullable()->comment('Mô tả chi tiết');
            $table->string('slug', 120)->nullable()->comment('Slug (nếu cần, thường không cần cho table area)');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_templates');
    }
};
