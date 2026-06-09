<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageRequest extends Model
{
    protected $fillable = ['requester_id', 'requested_user_id', 'status'];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requestedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_user_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Returns a flipped array of target user IDs that $requesterId has an approved request for.
     * Used for O(1) lookup in lists to avoid N+1 queries.
     *
     * @param  int[]  $targetIds
     */
    public static function approvedViewersOf(int $requesterId, array $targetIds): array
    {
        if (empty($targetIds)) {
            return [];
        }

        return self::where('requester_id', $requesterId)
            ->whereIn('requested_user_id', $targetIds)
            ->where('status', 'approved')
            ->pluck('requested_user_id')
            ->flip()
            ->all();
    }
}
