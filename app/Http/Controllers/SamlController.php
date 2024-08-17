<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Aacotroneo\Saml2\Http\Controllers\Saml2Controller;
use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Saml2Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SamlController extends Saml2Controller
{
    /**
     * Generate local sp metadata.
     *
     * @param Saml2Auth $saml2Auth
     * @return \Illuminate\Http\Response
     */
    public function metadata(Saml2Auth $saml2Auth)
    {
        $metadata = $saml2Auth->getMetadata();
        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Process an incoming saml2 assertion request.
     * Fires 'Saml2LoginEvent' event if a valid user is found.
     *
     * @param Saml2Auth $saml2Auth
     * @param $idpName
     * @return \Illuminate\Http\Response
     */
    public function acs(Saml2Auth $saml2Auth, $idpName)
    {
        $errors = $saml2Auth->acs();

        if (!empty($errors)) {
            logger()->error('Saml2 error_detail', ['error' => $saml2Auth->getLastErrorReason()]);
            session()->flash('saml2_error_detail', [$saml2Auth->getLastErrorReason()]);

            logger()->error('Saml2 error', $errors);
            session()->flash('saml2_error', $errors);
            return redirect(config('saml2_settings.errorRoute'));
        }

        $user = $saml2Auth->getSaml2User();
        $attributes = $user->getAttributes();

        // Ensure that the required attributes are present
        $requiredAttributes = [
            'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname',
            'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname',
            'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'
        ];

        foreach ($requiredAttributes as $attribute) {
            if (!isset($attributes[$attribute][0])) {
                Log::error('SAML2Auth: Missing required attribute', ['attribute' => $attribute]);
                return redirect(config('saml2_settings.errorRoute'))->withErrors('Missing required SAML attributes.');
            }
        }

        // Extract attributes
        $firstname = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'][0];
        $lastname = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname'][0];
        $email = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'][0];
        $phone = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/telephoneNumber'][0] ?? null;
        $avatar = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/photo'][0] ?? 'avatar.jpg';
        $password = Str::random(16);

        // Check if the user already exists
        $existingUser = \App\Models\User::where('email', $email)->first();

        if (!$existingUser) {
            $newUser = \App\Models\User::create([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'email_verified_at' => Carbon::now(), // Ensure this is properly set
                'phone' => $phone,
                'password' => Hash::make($password),
                'avatar' => $avatar,
                'internal' => 1,
                'status_id' => 1,
                'role_id' => 2,
                'resident' => 1,
                'company_id' => 1,
            ]);

            Auth::login($newUser);
        } else {
            Auth::login($existingUser);
        }

        if (Auth::check()) {
            $redirectUrl = $user->getIntendedUrl() ?: route('home');
            return redirect($redirectUrl);
        } else {
            return redirect(config('saml2_settings.errorRoute'))->withErrors('Authentication failed.');
        }
    }

    /**
     * Process an incoming saml2 logout request.
     * Fires 'Saml2LogoutEvent' event if its valid.
     * This means the user logged out of the SSO infrastructure, you 'should' log them out locally too.
     *
     * @param Saml2Auth $saml2Auth
     * @param $idpName
     * @return \Illuminate\Http\Response
     */
    public function sls(Saml2Auth $saml2Auth, $idpName)
    {
        $errors = $saml2Auth->sls($idpName, config('saml2_settings.retrieveParametersFromServer'));

        if (!empty($errors)) {
            Log::error('SAML2Auth: Errors encountered during SAML SLS processing.', [
                'errors' => $errors,
            ]);
            session()->flash('saml2_error', $errors);
            throw new \Exception("Could not log out");
        }

        Log::info('SAML2Auth: Successfully logged out.');
        return redirect(config('saml2_settings.logoutRoute'));
    }

    /**
     * Initiate a logout request across all the SSO infrastructure.
     *
     * @param Saml2Auth $saml2Auth
     * @param Request $request
     */
    public function logout(Saml2Auth $saml2Auth, Request $request)
    {
        $returnTo = $request->query('returnTo');
        $sessionIndex = $request->query('sessionIndex');
        $nameId = $request->query('nameId');
        $saml2Auth->logout($returnTo, $nameId, $sessionIndex); //will actually end up in the sls endpoint
        //does not return
    }

    /**
     * Initiate a login request.
     *
     * @param Saml2Auth $saml2Auth
     */
    public function login(Saml2Auth $saml2Auth)
    {
        $saml2Auth->login(config('saml2_settings.loginRoute'));
    }
}
