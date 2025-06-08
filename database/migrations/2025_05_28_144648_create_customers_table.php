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
        Schema::create('customers', function (Blueprint $table) {
            $table->id()->comment('Mã khách hàng');

            $table->string('full_name', 100)->comment('Họ và tên');
            $table->string('avatar')->nullable()->comment('Ảnh đại diện');

            $table->string('phone_number', 20)->unique()->comment('Số điện thoại');
            $table->string('email', 100)->unique()->nullable()->comment('Email');
            $table->timestamp('email_verified_at')->nullable()->comment('Thời điểm xác thực email');

            $table->string('password')->nullable()->comment('Mật khẩu (nếu có tài khoản)');
            $table->string('google_id')->nullable()->unique()->comment('ID đăng nhập Google');
            $table->string('facebook_id')->nullable()->unique()->comment('ID đăng nhập Facebook');

            $table->text('address')->nullable()->comment('Địa chỉ');
            $table->date('date_of_birth')->nullable()->comment('Ngày sinh');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->comment('Giới tính');

            $table->string('city_id', 10)->nullable()->comment('ID Thành phố từ API địa giới hành chính');
            $table->string('district_id', 10)->nullable()->comment('ID Quận/Huyện từ API địa giới hành chính');
            $table->string('ward_id', 10)->nullable()->comment('ID Phường/Xã từ API địa giới hành chính');

            $table->json('tags')->nullable()->comment('Các tag cho khách hàng (VD: ["khách VIP", "dị ứng hải sản"])');
            $table->text('notes')->nullable()->comment('Ghi chú nội bộ');

            $table->enum('status', ['active', 'inactive', 'pending_activation', 'blocked'])->default('active')->comment('Trạng thái tài khoản');
            $table->rememberToken()->comment('Token ghi nhớ đăng nhập');
            $table->softDeletes()->comment('Xoá mềm');
            $table->timestamps();

            $table->comment('Thông tin khách hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
