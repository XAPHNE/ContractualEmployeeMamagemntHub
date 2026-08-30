<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    protected $fillable = [
        'api_key_id',
        'client_name',
        'ip_address',
        'method',
        'endpoint',
        'request_params',
        'status_code',
        'records_count',
        'duration_ms',
    ];

    protected $casts = [
        'request_params' => 'array',
        'status_code' => 'integer',
        'records_count' => 'integer',
        'duration_ms' => 'float',
    ];

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
