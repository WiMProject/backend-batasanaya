<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * Menentukan bahwa primary key bukan integer auto-increment.
     */
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Kolom yang bisa diisi secara massal.
     */
    protected $fillable = ['id', 'name'];

    /**
     * Mendefinisikan relasi bahwa satu Role bisa dimiliki oleh banyak User.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}