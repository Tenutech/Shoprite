<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BlockedNumber;
use Illuminate\Support\Facades\Log;

class BlockPhoneNumbers
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the incoming request is from a blocked phone number.
     * If the number is blocked, the middleware silently ends the request (returns nothing).
     * Otherwise, it passes the request to the next layer (controller).
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request object
     * @param  \Closure  $next  The next middleware/controller in the pipeline
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Decode the incoming JSON payload sent by Meta API
        $data = $request->json()->all();

        // Attempt to extract the phone number of the sender from the payload
        // Typical structure for WhatsApp Business API incoming messages
        $from = $data['entry'][0]['changes'][0]['value']['messages'][0]['from'] ?? null;

        // If a phone number is found and exists in the blocked_numbers table, silently ignore the request
        if ($from && BlockedNumber::where('phone_number', $from)->exists()) {
            // Optional: Log this attempt for internal visibility (can be removed in production)
            // Log::info("Blocked number attempted to send message: " . $from);

            // Do nothing — return empty 200 OK response without processing
            return response()->json([], 200);
        }

        // If the number is not blocked, continue to the controller
        return $next($request);
    }
}