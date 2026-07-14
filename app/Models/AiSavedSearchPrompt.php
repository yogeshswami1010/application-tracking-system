<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSavedSearchPrompt extends Model
{
    protected $table = 'ai_saved_search_prompts';

    protected $fillable = [
        'user_id',
        'query_text',
        'label',
        'use_count',
        'is_favorite',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    public function incrementUseCount()
    {
        $this->increment('use_count');
        $this->touch();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}