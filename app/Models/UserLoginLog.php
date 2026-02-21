<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginLog extends Model
{
    protected $fillable = ['user_id', 'ip_address', 'country', 'country_code',
        'city', 'isp', 'is_vpn', 'user_agent', 'logged_at', ];

    protected $casts = [
        'is_vpn' => 'boolean',
        'logged_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
