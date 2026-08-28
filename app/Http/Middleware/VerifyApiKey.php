<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            $token = $request->header('X-API-KEY');
        }

        if (!$token) {
            return response()->json(['message' => 'Unauthorized. API Key is missing.'], 401);
        }

        $apiKey = ApiKey::where('key', $token)->where('is_active', true)->first();

        if (!$apiKey) {
            return response()->json(['message' => 'Unauthorized. Invalid or inactive API Key.'], 401);
        }

        // Update last used
        $apiKey->update(['last_used_at' => now()]);

        return $next($request);
    }
}
