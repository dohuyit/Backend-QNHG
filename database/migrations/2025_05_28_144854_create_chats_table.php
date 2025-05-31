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
        Schema::create('chats', function (Blueprint $table) {
            $table->id()->comment('Mã cuộc trò chuyện');
            $table->string('customer_identifier', 100)->comment('Định danh khách hàng');
            $table->foreignId('customer_id')->nullable()->comment('Link tới Customers')->constrained('customers', 'id')->onDelete('set null');
            $table->enum('status', ['open', 'assigned', 'closed', 'pending'])->default('pending')->comment('Trạng thái');
            $table->foreignId('user_id')->nullable()->comment('Nhân viên CSKH xử lý')->constrained('users', 'id')->onDelete('set null');
            $table->timestamp('last_message_at')->nullable()->comment('TG tin nhắn cuối');
            $table->timestamp('closed_at')->nullable()->comment('TG đóng chat');
            $table->string('channel', 50)->default('website')->comment('Kênh chat');
            $table->timestamps();
            $table->comment('Thông tin cuộc trò chuyện với khách hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
