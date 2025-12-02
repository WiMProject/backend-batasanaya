<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasangkanHurufProgress extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'pasangkanhuruf_progress';

    protected $fillable = [
        'id', 
        'user_id', 
        'level_number',
        'is_unlocked',
        'is_completed',
        'attempts'
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'is_completed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
