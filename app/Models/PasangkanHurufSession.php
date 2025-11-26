<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasangkanHurufSession extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'pasangkanhuruf_sessions';

    protected $fillable = [
        'id', 'user_id', 'level_number', 'score', 'time_taken',
        'correct_matches', 'wrong_matches', 'stars', 'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
