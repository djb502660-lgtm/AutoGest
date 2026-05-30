<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotFaq extends Model
{
    protected $fillable = [
        'category',
        'question',
        'answer',
        'keywords',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
