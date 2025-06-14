<?php

namespace App\Exceptions;

use Exception;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends Exception
{
    // 404 error handler
    public function render($request, Exception $exception)
    {
        if ($exception instanceof NotFoundHttpException) {
            return Inertia::render('Errors/404', [
                'status' => 404,
                'message' => 'Page not found',
            ]);
        }
        return parent::render($request, $exception);
    }
}
