<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegisteredUser extends Model
{
    public const STATUS_STARTED = 'started';

    public const STATUS_EMAIL_PENDING = 'email_pending';

    public const STATUS_EMAIL_VERIFIED = 'email_verified';

    public const STATUS_SMS_SENT = 'sms_sent';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'status',
        'role',
        'name',
        'email',
        'phone',
        'postcode',
        'registration_ip',
        'session_id',
        'device_id',
        'persistent_id',
        'hardware_profile_hash',
        'fingerprint_hash',
        'user_agent',
        'os_name',
        'browser_name',
        'platform',
        'sms_sent_count',
        'email_verified_at',
        'last_sms_sent_at',
        'completed_at',
        'completed_user_id',
        'block_reason',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_sms_sent_at' => 'datetime',
            'completed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function completedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_user_id');
    }
}
