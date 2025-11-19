<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LetterPair extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id', 'letter_name', 'outline_image', 'complete_image', 
        'difficulty_level', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
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

    public function getOutlineUrlAttribute()
    {
        return url("api/letter-pairs/{$this->id}/outline");
    }

    public function getCompleteUrlAttribute()
    {
        return url("api/letter-pairs/{$this->id}/complete");
    }
}