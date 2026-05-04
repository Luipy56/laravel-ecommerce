<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'sort_order',
        'is_active',
        'question_ca',
        'question_es',
        'question_en',
        'answer_ca',
        'answer_es',
        'answer_en',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
