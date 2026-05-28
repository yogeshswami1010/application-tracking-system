<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'input_cost',
        'output_cost',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
            'input_cost' => 'float',
            'output_cost' => 'float',
            'total_cost' => 'float',
        ];
    }
}
