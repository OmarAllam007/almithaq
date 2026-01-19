<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProfileImage extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileImageFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_path',
        'thumbnail_path',
        'is_main',
        'is_approved',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'is_approved' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    public function setAsMain(): void
    {
        // Remove is_main from all other images for this user
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_main' => false]);

        // Set this image as main
        $this->update(['is_main' => true]);
    }

    public function approve(): void
    {
        $this->update(['is_approved' => true]);
    }

    public function reject(): void
    {
        $this->update(['is_approved' => false]);
    }

    public function getThumbnailUrlAttribute(): string
    {

        return route('profile.images.thumbnail', $this);
    }

    protected static function booted(): void
    {
        static::deleting(function (ProfileImage $image) {
            // Delete files when model is deleted
            Storage::disk('local')->delete($image->image_path);
            Storage::disk('local')->delete($image->thumbnail_path);
        });
    }
}
