<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'full_name',
        'avatar',
        'phone_number',
        'email',
        'email_verified_at',
        'password',
        'google_id',
        'facebook_id',
        'address',
        'date_of_birth',
        'gender',
        'city_id',
        'district_id',
        'ward_id',
        'tags',
        'notes',
        'status',
        'remember_token',
    ];
}
