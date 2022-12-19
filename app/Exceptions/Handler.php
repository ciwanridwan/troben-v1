<?php

namespace App\Exceptions;

use Throwable;
use App\Http\Response;
use PDOException;
use libphonenumber\NumberParseException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response as LaravelResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        FileNotFoundException::class,
        OtpVerifyException::class,
        OutOfRangePricingException::class,
        UserUnauthorizedException::class,
        NumberParseException::class,
        InvalidDataException::class,
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
        // if ($this->shouldReport($e) && app()->bound('sentry')) {
        //     app('sentry')->captureException($e);
        // }

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
            } elseif ($e instanceof PDOException) {
                if(strstr($e->getMessage(), 'SQLSTATE[')) {
                    preg_match('/SQLSTATE\[(\w+)\]: (.*)/', $e->getMessage(), $matches);
                    if (count($matches) >= 3) {
                        $code = $matches[1];
                        $message = sprintf('%s: %s', $code, $matches[2]);
                        $e = new Error(Response::RC_DATABASE_ERROR, ['message' => $message]);
                    } else if (strpos($e->getMessage(), 'SQLSTATE[08006]') !== false) {
                        $e = new Error(Response::RC_DATABASE_ERROR);
                    }
                }
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
            // if ($this->shouldReport($e) && app()->bound('sentry')) {
            //     app('sentry')->captureException($e);
            // }
        });
    }
}
