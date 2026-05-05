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
        'iterations_count',
        'improvement_feedback',
        'improvement_feedback_at',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'problem_description' => 'encrypted',
            'resolution' => 'encrypted',
            'improvement_feedback' => 'encrypted',
            'is_active' => 'boolean',
            'improvement_feedback_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PersonalizedSolution $solution): void {
            if ($solution->public_token === null || $solution->public_token === '') {
                $solution->public_token = bin2hex(random_bytes(32));
            }
        });
    }

    public function portalUrl(): string
    {
        return url('/client/personalized-solutions/'.$this->public_token);
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
