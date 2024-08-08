<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyJiraWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $secret = config('services.jira.secret');

        $signature = $request->header('x-hub-signature');
        $payload = $request->getContent();
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        if ($signature !== $expectedSignature) {
            Log::warning('Invalid Jira webhook signature.', [
                'provided' => $signature,
                'expected' => $expectedSignature
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        return $next($request);
    }
}
