<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Exception;
use Request;
use Msg;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        // AuthorizationException::class,
        HttpException::class,
        // ModelNotFoundException::class,
        // ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e) {
            
            $url = Request::url();

            $message = $e->getMessage();
            $errline = $e->getLine();
            $errfile = $e->getFile();


            if ($e instanceof TokenMismatchException ) {
                $message = 'Token Mismatch. Please reload the page.';
            }

            if ($e instanceof NotFoundHttpException ) {
                $message = "Requested URL: [{$url}] was not found.";
            }

            if (ENVIRONMENT === 'production' or config('app.debug') === false) {
                $message = 'Something went terribly wrong.';
            }


            // Writes all the reported errors 
            // to error log file located
            // @/storage/logs
            $error_log_summary = array(
                'Trace' => date(TIMESTAMP_FULL_FORMAT),
                'Error Code' => $e->getCode(),
                'Message' => $message,
                'Line' => $errline,
                'File' => $errfile,
                'Source' => $url
            );

            log_write('errors', $error_log_summary);

            $error = Msg::error($error_log_summary);


            return (is_array($error)) ? response()->json($error) : response()->view('errors.page', array(
                'message' => $message,
                'line' => $errline,
                'file' => $errfile
            ));
        }
   
        return parent::render($request, $e);
    }
}
