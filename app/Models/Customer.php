<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'full_name',
        'avatar',
        'phone_number',
        'email',
        'password',
        'google_id',
        'facebook_id',
        'address',
        'date_of_birth',
        'gender',
        'tags',
        'notes',
        'is_active',
        'email_verified_at',
        'remember_token',
        'deleted_at',
    ];
}
