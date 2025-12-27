<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'phone_number', 'code', 'is_used', 'is_revoked', 'expired_at',
    ];

    // Kolom 'expired_at' akan otomatis di-handle sebagai objek Carbon
    protected $dates = ['expired_at'];

    protected $hidden = ['code'];

    public function user()
    {
        return $this->belongsTo(User::class, 'phone_number', 'phone_number');
    }
}