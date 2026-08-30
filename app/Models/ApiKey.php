<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'key',
        'allowed_ips',
        'rate_limit_per_minute',
        'is_active',
        'last_used_at',
        'expires_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'rate_limit_per_minute' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (ApiKey $apiKey) {
            if (empty($apiKey->key)) {
                $apiKey->key = 'cemh_live_' . Str::random(40);
            }
            if (auth()->check()) {
                $apiKey->created_by ??= auth()->id();
                $apiKey->updated_by ??= auth()->id();
            }
        });

        static::updating(function (ApiKey $apiKey) {
            if (auth()->check()) {
                $apiKey->updated_by = auth()->id();
            }
        });
    }

    public static function generateKey(): string
    {
        return 'cemh_live_' . Str::random(40);
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isIpAllowed(?string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true; // No IP restriction
        }

        $allowedList = array_map('trim', explode(',', $this->allowed_ips));

        return in_array($ip, $allowedList, true);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ApiLog::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
