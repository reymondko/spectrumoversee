<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Mail;
use App\Mail\ErrorEmail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;



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

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {

       /* if($exception instanceof FatalThrowableError ||
           $exception instanceof MethodNotAllowedHttpException
          ){
            $mail = new \stdClass();
            $mail->message = $exception;
            $mail->sender = 'Spectrum Oversee';
            $mail->subject = 'Spectrum Oversee Error';
            Mail::to('mark@digiance.com')->send(new ErrorEmail($mail));
            parent::report($exception);
        }else{*/
            parent::report($exception);
        //}



    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        /*
        // Handler for exceptions on Logiwa API routes
        if($request->is('api/logiwa/*')){
            return response()->json([
                'error_message' => $exception->getMessage()
            ],500);
        }

        if($exception instanceof FatalThrowableError){
            return response()->view('error.error', [], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->view('error.error', [], 404);
        }

        if ($exception instanceof \Swift_TransportException) {
            return response()->view('error.error', [], 404);
        }

        if ($exception instanceof \ErrorException) {
            return response()->view('error.error', [], 404);
        }
    */
        return parent::render($request, $exception);
    }
}
