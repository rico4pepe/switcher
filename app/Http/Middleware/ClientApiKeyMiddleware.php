<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientApiKeyMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ) {

        $apiKey = $request->header(
            'X-API-KEY'
        );

        if (! $apiKey) {

            return response()->json([
                'message' => 'API key missing.'
            ], 401);
        }

        $client = Client::query()

            ->where('api_key', $apiKey)

            ->where('is_active', true)

            ->first();

        if (! $client) {

            return response()->json([
                'message' => 'Invalid API key.'
            ], 401);
        }

        $request->attributes->set(
            'client',
            $client
        );

        return $next($request);
    }
}
