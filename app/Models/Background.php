<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Background extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'file', 'size', 'created_by_id',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}