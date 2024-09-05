<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * This method is responsible for catching the TokenMismatchException
     * (which occurs when the session has expired) and redirecting the user
     * to the root page (welcome page) with a flash message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function render($request, Throwable $exception)
    {
        // Handle session expiration
        if ($exception instanceof TokenMismatchException) {
            // Redirect the user to the root route with a message when the session has expired
            return redirect()->route('root')->with('message', 'Session expired, please log in again.');
        }

        // Default exception handling
        return parent::render($request, $exception);
    }
}
