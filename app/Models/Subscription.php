<?php

namespace App\Models;

use App\Models\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_duration',
        'price',
        'status',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'price' => 'decimal:2',
            'plan_duration' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE
            && $this->expires_at
            && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === SubscriptionStatus::EXPIRED
            || ($this->expires_at && $this->expires_at->isPast());
    }

    public function daysRemaining(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        return max(0, now()->diffInDays($this->expires_at, false));
    }
}
