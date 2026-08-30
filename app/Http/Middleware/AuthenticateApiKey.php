<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\ApiLog;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // 1. Extract API token
        $rawKey = $request->header('X-API-KEY')
            ?? $request->bearerToken()
            ?? $request->query('api_key');

        if (! $rawKey) {
            $this->logRequest(null, $request, 401, 0, $startTime);

            return response()->json([
                'status' => 'error',
                'code' => 'UNAUTHORIZED',
                'message' => 'API Key is missing. Provide it via X-API-KEY header or Bearer Token.',
            ], 401);
        }

        // 2. Lookup API Key
        $apiKey = ApiKey::where('key', $rawKey)->first();

        if (! $apiKey || ! $apiKey->isValid()) {
            $this->logRequest($apiKey, $request, 401, 0, $startTime);

            return response()->json([
                'status' => 'error',
                'code' => 'UNAUTHORIZED',
                'message' => 'Invalid or expired API Key.',
            ], 401);
        }

        // 3. Check IP Whitelist
        if (! $apiKey->isIpAllowed($request->ip())) {
            $this->logRequest($apiKey, $request, 403, 0, $startTime);

            return response()->json([
                'status' => 'error',
                'code' => 'IP_FORBIDDEN',
                'message' => "Client IP address '{$request->ip()}' is not whitelisted for this API key.",
            ], 403);
        }

        // 4. Rate Limiting per API Key
        $rateLimitKey = 'api_key:' . $apiKey->id;
        $maxAttempts = $apiKey->rate_limit_per_minute ?: 60;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $this->logRequest($apiKey, $request, 429, 0, $startTime);

            return response()->json([
                'status' => 'error',
                'code' => 'TOO_MANY_REQUESTS',
                'message' => "Rate limit exceeded. Please retry in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        // 5. Proceed to endpoint
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        // Calculate records count if JSON response
        $recordsCount = 0;
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            if (isset($data['count'])) {
                $recordsCount = (int) $data['count'];
            } elseif (isset($data['data']) && is_array($data['data'])) {
                $recordsCount = count($data['data']);
            } elseif (! empty($data)) {
                $recordsCount = 1;
            }
        }

        // 6. Audit Logging & Last Used update
        $apiKey->updateQuietly(['last_used_at' => now()]);
        $this->logRequest($apiKey, $request, $response->getStatusCode(), $recordsCount, $startTime);

        return $response;
    }

    protected function logRequest(?ApiKey $apiKey, Request $request, int $statusCode, int $recordsCount, float $startTime): void
    {
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        $params = $request->except(['api_key', 'password', 'key']);

        ApiLog::create([
            'api_key_id' => $apiKey?->id,
            'client_name' => $apiKey?->name ?? 'Unknown / Unauthenticated',
            'ip_address' => $request->ip(),
            'method' => $request->method(),
            'endpoint' => $request->path(),
            'request_params' => ! empty($params) ? $params : null,
            'status_code' => $statusCode,
            'records_count' => $recordsCount,
            'duration_ms' => $durationMs,
        ]);
    }
}
