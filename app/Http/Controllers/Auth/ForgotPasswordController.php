<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */
    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email field.
        $request->validate([
            'email' => 'required|email',
        ]);

        // Look up the user by email.
        $user = User::where('email', $request->email)->first();

        // Ensure the user exists and has a verified email.
        if (!$user || !$user->hasVerifiedEmail()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => ['Your email address is not verified.']
                ], 422);
            }
            return back()->withErrors([
                'message' => 'Your email address is not verified.',
            ]);
        }

        // Use the default password broker to send the reset link.
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        if ($request->ajax()) {
            if ($response === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'status' => trans($response),
                    'message' => 'A password reset link has been sent to your email address. Please check your inbox for further instructions.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => [trans($response)]
                ], 422);
            }
        }

        return $response === Password::RESET_LINK_SENT
            ? back()->with('status', trans($response))
            : back()->withErrors(['message' => trans($response)]);
    }
}
