<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';
    public const ROLE_CLIENT = 'client';

    public const ROLES = [
        self::ROLE_ADMIN => '管理者',
        self::ROLE_STAFF => '運営担当',
        self::ROLE_CLIENT => 'クライアント',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'agency_id',
        'company_id',
        'last_login_at',
        'invitation_token',
        'invitation_sent_at',
        'invitation_accepted_at',
        'disabled_at',
        'gbp_access_token',
        'gbp_refresh_token',
        'gbp_token_expires_at',
        'gbp_account_email',
        'gbp_account_info',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'gbp_access_token',
        'gbp_refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'invitation_sent_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
            'disabled_at' => 'datetime',
            'gbp_token_expires_at' => 'datetime',
            'gbp_account_info' => 'array',
        ];
    }

    public function hasGbpConnected(): bool
    {
        return $this->gbp_access_token !== null;
    }

    public function isGbpTokenExpired(): bool
    {
        return $this->gbp_token_expires_at && $this->gbp_token_expires_at->isPast();
    }

    public function isPendingInvitation(): bool
    {
        return $this->invitation_token !== null && $this->invitation_accepted_at === null;
    }

    public function isDisabled(): bool
    {
        return $this->disabled_at !== null;
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    /** admin または staff */
    public function isInternal(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_STAFF], true);
    }
}
