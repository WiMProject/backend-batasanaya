<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

// Untuk JWT
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;

    // Menentukan bahwa primary key bukan integer auto-increment
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'full_name', 'email', 'phone_number', 'password', 'pin_code', 'role_id',
    ];

    protected $hidden = [
        'password', 'pin_code',
    ];
    
    // Implementasi method untuk JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    
    // Mendefinisikan relasi ke Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Mendefinisikan relasi one-to-one ke UserSubscription.
     */
    public function subscription()
    {
        return $this->hasOne(UserSubscription::class);
    }

    /**
     * Mendefinisikan relasi one-to-one ke UserPreference.
     */
    public function preference()
    {
        return $this->hasOne(UserPreference::class, 'id', 'id');
    }
}