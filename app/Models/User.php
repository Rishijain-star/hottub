<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_USER = 'user';
    const ROLE_DEALER = 'dealer';
    const ROLE_MANUFACTURER = 'manufacturer';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUB_ADMIN = 'sub_admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'admin_permissions',
        'status',
        'credits',
        'company_name',
        'company_number',
        'vat_number',
        'phone',
        'postcode',
        'address',
        'website',
        'profile_picture',
        'type',
        'service_offerings',
        'dealer_lat',
        'dealer_lng',
        'manufacturer_lat',
        'manufacturer_lng',
        'phone_verified_at',
        'sms_otp_hash',
        'sms_otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'service_offerings' => 'array',
            'admin_permissions' => 'array',
            'credits' => 'integer',
            'phone_verified_at' => 'datetime',
            'sms_otp_expires_at' => 'datetime',
        ];
    }

    public function leadPurchases()
    {
        return $this->hasMany(LeadPurchase::class);
    }

    public function purchasedLeads()
    {
        return $this->belongsToMany(Lead::class, 'lead_purchases');
    }

    protected $attributes = [
        'role' => self::ROLE_USER,
        'status' => 'active',
        'credits' => 0,
    ];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN || $this->role === self::ROLE_SUB_ADMIN;
    }

    public function isFullAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSubAdmin(): bool
    {
        return $this->role === self::ROLE_SUB_ADMIN;
    }

    /**
     * Dangerous actions: payments, creating dealers/manufacturers — only full admin unless explicitly granted.
     */
    public function canAdminPermission(string $key): bool
    {
        if ($this->isFullAdmin()) {
            return true;
        }
        if (!$this->isSubAdmin()) {
            return false;
        }
        $perms = $this->admin_permissions ?? [];

        return !empty($perms[$key]);
    }

    public function isDealer(): bool
    {
        return $this->role === self::ROLE_DEALER;
    }

    public function isManufacturer(): bool
    {
        return $this->role === self::ROLE_MANUFACTURER;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}
