<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalizedSolution extends Model
{
    protected $table = 'personalized_solutions';

    protected $fillable = [
        'client_id',
        'order_id',
        'email',
        'phone',
        'address_street',
        'address_city',
        'address_province',
        'address_postal_code',
        'address_note',
        'problem_description',
        'resolution',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PersonalizedSolutionAttachment::class, 'personalized_solution_id')->where('is_active', true);
    }

    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_CLIENT_CONTACTED = 'client_contacted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
}
