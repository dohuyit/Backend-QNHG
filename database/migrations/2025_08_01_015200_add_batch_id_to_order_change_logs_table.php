<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_change_logs', function (Blueprint $table) {
            $table->string('batch_id', 64)->nullable()->after('order_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('order_change_logs', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }
};
