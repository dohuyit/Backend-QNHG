<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
    protected $table = 'bill_payments';
    protected $fillable = [
        'bill_id',
        'payment_method',       // 'cash', 'credit_card', ...
        'amount_paid',
        'payment_time',         // Thường tự động set
        'transaction_ref',      // Mã giao dịch từ cổng thanh toán
        'user_id',              // Nhân viên nhận thanh toán
        'notes',
    ];
}
