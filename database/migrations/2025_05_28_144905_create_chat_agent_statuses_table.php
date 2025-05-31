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
        Schema::create('chat_agent_statuses', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->comment('Mã nhân viên CSKH')->constrained('users', 'id')->onDelete('cascade');
            $table->enum('status', ['online', 'offline', 'busy', 'away'])->default('offline')->comment('Trạng thái');
            $table->timestamp('last_seen')->nullable()->comment('Lần cuối online');
            $table->integer('max_concurrent_chats')->default(3)->comment('Số chat tối đa/lần');
            $table->integer('current_chat_count')->default(0)->comment('Số chat đang xử lý');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->comment('Trạng thái làm việc của nhân viên CSKH');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_agent_statuses');
    }
};
