<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed'
            ],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array
     */
    protected function messages()
    {
        return [
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }

    /**
     * Get a validator for an incoming password reset request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validator = Validator::make($data, $this->rules(), $this->messages());

        // Add custom failure messages for each password requirement using the `after` method
        $validator->after(function ($validator) use ($data) {
            if (!preg_match('/[A-Z]/', $data['password'])) {
                $validator->errors()->add('password', 'The password must contain at least one uppercase letter.');
            }
            if (!preg_match('/[a-z]/', $data['password'])) {
                $validator->errors()->add('password', 'The password must contain at least one lowercase letter.');
            }
            if (!preg_match('/[0-9]/', $data['password'])) {
                $validator->errors()->add('password', 'The password must contain at least one number.');
            }
            if (!preg_match('/[@$!%*?&]/', $data['password'])) {
                $validator->errors()->add('password', 'The password must contain at least one special character (@$!%*?&).');
            }
        });

        return $validator;
    }

    /**
     * Handle a password reset request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        // Validate using the custom validator
        $this->validator($request->all())->validate();

        // Attempt to reset the user's password. If successful, resetPassword will be called.
        $response = $this->broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // Return response based on the password reset attempt
        return $response == Password::PASSWORD_RESET
                    ? $this->sendResetResponse($request, $response)
                    : $this->sendResetFailedResponse($request, $response);
    }
}
