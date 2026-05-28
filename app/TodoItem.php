<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TodoItem extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'tag',
        'due_date',
        'position',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
