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
        'api_controller',
        'key',
        'allowed_ips',
        'rate_limit_per_minute',
        'is_active',
        'last_used_at',
        'expires_at',
        'created_by',
        'updated_by',
    ];

    public const CONTROLLERS = [
        \App\Http\Controllers\Api\MmlsayApiController::class => [
            'name' => 'MmlsayApiController (MMLSAY Health Insurance)',
            'endpoint' => '/api/mmlsay/employee',
        ],
        \App\Http\Controllers\Api\EmployeeApiController::class => [
            'name' => 'EmployeeApiController (Employee Directory)',
            'endpoint' => '/api/employees',
        ],
        \App\Http\Controllers\Api\DdoApiController::class => [
            'name' => 'DdoApiController (DDO Management)',
            'endpoint' => '/api/ddos',
        ],
    ];

    public static function getControllerOptions(): array
    {
        return collect(self::CONTROLLERS)->mapWithKeys(function ($details, $class) {
            return [$class => $details['name']];
        })->all();
    }

    public static function getUrlForController(?string $controller): ?string
    {
        if (! $controller) {
            return null;
        }

        if (isset(self::CONTROLLERS[$controller])) {
            return url(self::CONTROLLERS[$controller]['endpoint']);
        }

        // Support short class name match
        foreach (self::CONTROLLERS as $class => $details) {
            if (class_basename($class) === class_basename($controller)) {
                return url($details['endpoint']);
            }
        }

        return null;
    }

    public function getEndpointUrlAttribute(): ?string
    {
        return static::getUrlForController($this->api_controller);
    }

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
                $apiKey->key = 'apgcl_mmlsay_live_' . Str::random(40);
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
        return 'apgcl_mmlsay_live_' . Str::random(40);
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
