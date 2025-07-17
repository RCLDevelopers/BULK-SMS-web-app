<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'twilio_sid',
        'twilio_auth_token',
        'twilio_from',
        'textsms_api_key',
        'textsms_sender_id',
    ];
    
    protected $attributes = [
        'role' => 'user',
        'is_active' => true,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'twilio_auth_token',
        'textsms_api_key',
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
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Check if the user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
    
    /**
     * Check if the user is an admin or super admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }
    
    /**
     * Check if the user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
    
    /**
     * Check if the user is active
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Get the contacts for the user.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the messages for the user.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
