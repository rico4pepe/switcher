<?php

namespace App\Http\Middleware;

use App\Models\ClientApiKey;
use Closure;
use Illuminate\Http\Request;

class ClientApiKeyMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ) {
        $apiKey = $request->header('X-API-KEY');

        if (! $apiKey) {
            return response()->json([
                'message' => 'API key missing.',
            ], 401);
        }

        $clientApiKey = ClientApiKey::query()
            ->where('key', $apiKey)
            ->where('is_active', true)
            ->with('client')
            ->first();

        if (
            ! $clientApiKey ||
            ! $clientApiKey->client ||
            ! $clientApiKey->client->is_active
        ) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], 401);
        }

        $request->attributes->set(
            'client',
            $clientApiKey->client
        );

        $request->attributes->set(
            'environment',
            $clientApiKey->environment
        );

        return $next($request);
    }
}
