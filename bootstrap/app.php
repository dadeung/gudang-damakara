<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use App\Http\Problem;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {})
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));

        // Ubah Galat Validasi 422 standard menjadi Problem Details JSON Baku
        $exceptions->render(function (ValidationException $e, Request $request) {
            return Problem::response($request, 422, 'validasi-gagal', 'Isi permintaan tidak valid', 
                'Satu atau lebih field tidak memenuhi ketentuan kontrak.', ['errors' => $e->errors()]);
        });
    })->create();
