<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath());
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            
            return back()->with('resent', true);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error sending verification email: ' . $e->getMessage());

            // Redirect back with an error message
            return back()->with('emailError', 'There was an error sending the verification email. Please try again later.');
        }
    }

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected function redirectTo()
    {
        $user = auth()->user();
        
        switch ($user->role_id) {
            case 1:
            case 2:
                $url = 'admin/home';
                break;
            case 3:
                $url = 'seller/home';
                break;
            case 4:
                $url = 'buyer/home';
                break;
            case 5:
                $url = 'advisor/home';
                break;
            case 6:
                $url = 'trader/home';
                break;
            default:
                $url = '/home';
                break;
        }

        // Default redirection if none of the above conditions match
        return $url;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}
