<?php

namespace App\Models;

use App\Models\Enums\NotificationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Hides notifications whose actor deleted their account. Users soft delete,
     * so the actor_id survives and the relation resolves to null. Notifications
     * that never had an actor (subscription renewals) stay visible.
     */
    public function scopeWithPresentActor(Builder $query): Builder
    {
        return $query->where(
            fn (Builder $q) => $q->whereNull('actor_id')->orWhereHas('actor')
        );
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
