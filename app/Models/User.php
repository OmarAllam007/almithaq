<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Redis;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'registration_type',
        'username', 'marriage_type', 'marriage_status', 'age', 'child_count',
        'residence', 'nationality', 'city', 'religion', 'ethnicity', 'weight',
        'height', 'skin_color', 'body_shape', 'devotion', 'prayer', 'smoking',
        'beard', 'education_level', 'financial_status', 'field_of_work', 'job',
        'monthly_income', 'health_status', 'about_partner', 'about_self',
        'full_name', 'country_code', 'phone_number', 'last_seen_at', 'is_active',
        'profile_completed', 'is_admin', 'is_verified', 'verification_video_path',
        'smart_search_filters', ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'age' => 'integer',
            'child_count' => 'integer',
            'weight' => 'decimal:2',
            'height' => 'decimal:2',
            'monthly_income' => 'integer',
            'last_seen_at' => 'datetime',
            'registration_type' => 'integer',
            'profile_completed' => 'boolean',
            'is_admin' => 'boolean',
            'is_verified' => 'boolean',
            'smart_search_filters' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    public function isVerified(): bool
    {
        return $this->is_verified === true;
    }

    public function conversations()
    {
        return Conversation::where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id);
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(UserLoginLog::class);
    }

    public function getLastLoginLog()
    {
        return $this->loginLogs()->latest('logged_at')->first();
    }

    public function conversationsAsUserOne(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_one_id');
    }

    public function conversationsAsUserTwo(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_two_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'user_id', 'favorited_user_id')
            ->withTimestamps();
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'favorited_user_id', 'user_id')
            ->withTimestamps();
    }

    public function ignores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ignores', 'user_id', 'ignored_user_id')
            ->withTimestamps();
    }

    public function ignoredBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ignores', 'ignored_user_id', 'user_id')
            ->withTimestamps();
    }

    public function visitedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'profile_visits', 'visited_user_id', 'visitor_id')
            ->withTimestamps()
            ->distinct();
    }

    public function profileImages(): HasMany
    {
        return $this->hasMany(ProfileImage::class);
    }

    public function sentImageRequests(): HasMany
    {
        return $this->hasMany(ImageRequest::class, 'requester_id');
    }

    public function receivedImageRequests(): HasMany
    {
        return $this->hasMany(ImageRequest::class, 'requested_user_id');
    }

    public function canViewImagesOf(int $userId): bool
    {
        return ImageRequest::where('requester_id', $this->id)
            ->where('requested_user_id', $userId)
            ->where('status', 'approved')
            ->exists();
    }

    public function imageRequestStatusFor(int $userId): ?string
    {
        return ImageRequest::where('requester_id', $this->id)
            ->where('requested_user_id', $userId)
            ->value('status');
    }

    public function mainProfileImage(): HasMany
    {
        return $this->hasMany(ProfileImage::class)->where('is_main', true);
    }

    public function isOnline(): bool
    {
        $score = Redis::zscore('online_users', $this->id);

        return $score !== null && $score >= now()->subMinutes(10)->timestamp;
    }

    public function isFavoritedBy(int $userId): bool
    {
        return $this->favoritedBy()->where('user_id', $userId)->exists();
    }

    public function isIgnored(int $userId): bool
    {
        return $this->ignoredBy()->where('user_id', $userId)->exists();
    }

    public function getConversationWith(int $userId): ?Conversation
    {
        return Conversation::where(function ($query) use ($userId) {
            $query->where('user_one_id', $this->id)
                ->where('user_two_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('user_one_id', $userId)
                ->where('user_two_id', $this->id);
        })->first();
    }

    public function updateLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }

    public function accountDeletion(): HasOne
    {
        return $this->hasOne(AccountDeletion::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', \App\Models\Enums\SubscriptionStatus::ACTIVE)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', \App\Models\Enums\SubscriptionStatus::ACTIVE)
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }
}
