<?php

namespace App\Exceptions;

use Throwable;
use App\Http\Response;
use libphonenumber\NumberParseException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response as LaravelResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /** {@inheritdoc} */
    public function report(Throwable $e)
    {
        if ($this->shouldReport($e) && app()->bound('sentry')) {
            app('sentry')->captureException($e);
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // override default laravel error to our own custom error format
        if ($request->expectsJson()) {
            if ($e instanceof ValidationException) {
                $e = new Error(Response::RC_INVALID_DATA, $e->errors(), $e);
            } elseif ($e instanceof AuthenticationException) {
                $e = new Error(Response::RC_UNAUTHENTICATED);
            } elseif ($e instanceof NumberParseException) {
                $e = new Error(Response::RC_INVALID_PHONE_NUMBER);
            } elseif ($e instanceof HttpException) {
                switch ($e->getStatusCode()) {
                    case LaravelResponse::HTTP_FORBIDDEN:
                        $e = new Error(Response::RC_UNAUTHORIZED, [], $e);
                        break;
                    case LaravelResponse::HTTP_UNAUTHORIZED:
                        $e = new Error(Response::RC_UNAUTHENTICATED, [], $e);
                        break;
                    case LaravelResponse::HTTP_NOT_FOUND:
                        $e = new Error(Response::RC_ROUTE_NOT_FOUND, [], $e);
                        break;
                }
            }
        }

        return parent::render($request, $e);
    }

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
}
