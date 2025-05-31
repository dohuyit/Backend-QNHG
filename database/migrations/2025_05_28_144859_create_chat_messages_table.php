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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id()->comment('Mã tin nhắn');
            $table->foreignId('chat_id')->comment('Mã cuộc trò chuyện')->constrained('chats', 'id')->onDelete('cascade');
            $table->enum('sender_type', ['customer', 'agent', 'system'])->comment('Người gửi');
            $table->string('sender_id', 100)->comment('ID người gửi');
            $table->text('message_text')->nullable()->comment('Nội dung text');
            $table->enum('message_type', ['text', 'image', 'file', 'notification'])->default('text')->comment('Loại tin nhắn');
            $table->string('attachment_url', 255)->nullable()->comment('URL file đính kèm');
            $table->timestamp('sent_at')->useCurrent()->comment('Thời gian gửi');
            $table->timestamp('read_at')->nullable()->comment('Thời gian đọc');
            $table->comment('Chi tiết tin nhắn trong cuộc trò chuyện');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
