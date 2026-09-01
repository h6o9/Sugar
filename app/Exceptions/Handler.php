<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if (!($request->is('api/*') || $request->expectsJson())) {
                return null;
            }
            $model = class_basename($e->getModel() ?: '');
            $message = $model === 'Order'
                ? 'Order not found for this account. Use the numeric id from GET /api/orders (field "id").'
                : 'Record not found.';

            return response()->json(['status' => false, 'message' => $message], 404);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if (!($request->is('api/*') || $request->expectsJson())) {
                return null;
            }
            $previous = $e->getPrevious();
            if ($previous instanceof ModelNotFoundException && class_basename($previous->getModel() ?: '') === 'Order') {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found for this account. Use the numeric id from GET /api/orders (field "id").',
                ], 404);
            }

            return null;
        });

        $this->renderable(function (\RuntimeException $e, $request) {
            $message = $e->getMessage();
            $handled = strpos($message, 'time to add items') !== false
                || strpos($message, 'Wholesale orders cannot be modified') !== false
                || strpos($message, 'You can no longer update this order') !== false
                || strpos($message, 'Please select the item') !== false
                || strpos($message, 'Order item not found') !== false;
            if (!$handled) {
                return null;
            }
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('my-cart')->with(['status' => false, 'message' => $message]);
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Send Authorization: Bearer {token}',
            ], 401);
        }

        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
