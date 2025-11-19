<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GameMatch extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id', 'game_session_id', 'letter_pair_id', 
        'is_correct', 'attempt_time'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'attempt_time' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }

    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }

    public function letterPair()
    {
        return $this->belongsTo(LetterPair::class);
    }
}