<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'file_name', 'type', 'file', 'size', 'created_by_id',
    ];

    /**
     * Mendefinisikan relasi ke user yang membuat asset ini.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}