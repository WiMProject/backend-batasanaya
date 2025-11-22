<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CariHijaiyyahSession extends Model
{
    protected $table = 'carihijaiyah_sessions';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'user_id',
        'level_number',
        'score',
        'time_taken',
        'correct_matches',
        'wrong_matches',
        'stars',
        'completed_at'
    ];
    
    protected $casts = [
        'level_number' => 'integer',
        'score' => 'integer',
        'time_taken' => 'integer',
        'correct_matches' => 'integer',
        'wrong_matches' => 'integer',
        'stars' => 'integer',
        'completed_at' => 'datetime'
    ];
    
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
