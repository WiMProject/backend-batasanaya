<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'audio_enabled', 'music_enabled', 'max_screen_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }
}