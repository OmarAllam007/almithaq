<?php

namespace App\Models;

use App\Models\Enums\DeleteAccountReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountDeletion extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'reason' => DeleteAccountReason::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
