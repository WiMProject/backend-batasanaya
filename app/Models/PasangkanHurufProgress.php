<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PasangkanHurufProgress extends Model
{
    protected $table = 'pasangkanhuruf_progress';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
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
        'attempts' => 'integer'
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
