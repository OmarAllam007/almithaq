<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAcquisition extends Model
{
    /**
     * The UTM parameters captured from campaign URLs. Adding a channel means
     * adding its key here, to the session payload, and to the migration.
     */
    public const PARAMETERS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    protected $fillable = ['user_id', ...self::PARAMETERS];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
