<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasApiTokens, HasFactory, SoftDeletes;
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
        'status_customer',
        'remember_token',
    ];

    /**
     * Relationship với Order
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
