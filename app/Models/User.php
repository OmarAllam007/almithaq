<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'registration_type',
        'username',
        'marriage_type',
        'marriage_status',
        'age',
        'child_count',
        'residence',
        'nationality',
        'city',
        'religion',
        'ethnicity',
        'weight',
        'height',
        'skin_color',
        'body_shape',
        'devotion',
        'prayer',
        'smoking',
        'beard',
        'education_level',
        'financial_status',
        'field_of_work',
        'job',
        'monthly_income',
        'health_status',
        'about_partner',
        'about_self',
        'full_name',
        'phone_number',
    ];

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
            'monthly_income' => 'decimal:2',
        ];
    }
}
