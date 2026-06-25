<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckAdmin::class,
            'desktop.only' => \App\Http\Middleware\RestrictToDesktop::class,
        ]);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->web(append: [
            \App\Http\Middleware\MobileViewMiddleware::class,
        ]);

        // Razorpay posts the webhook without a CSRF token; it is verified by signature.
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // User-safe checkout failures (empty cart, item out of stock, coupon gone)
        // — show the message rather than a raw 500.
        $exceptions->render(function (\App\Exceptions\CheckoutException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        });

        // For any other unhandled error on an AJAX/JSON request, return a clean
        // JSON envelope so the front-end fetch handlers can toast it instead of
        // failing silently. Never leak the raw exception message in production.
        $exceptions->render(function (\Throwable $e, $request) {
            if (($request->expectsJson() || $request->ajax())
                && !$e instanceof \Illuminate\Validation\ValidationException
                && !$e instanceof \Illuminate\Auth\AuthenticationException) {
                $status  = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                    ? $e->getStatusCode() : 500;
                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong. Please try again.';
                return response()->json(['success' => false, 'message' => $message], $status);
            }
            return null; // fall through to the default (renders resources/views/errors/*)
        });
    })->create();

// On Hostinger: madhavi-app/ and public_html/ are siblings inside ~/domains/madhavistores.in/
// dirname(__DIR__, 2) = ~/domains/madhavistores.in — so public_html is right next to madhavi-app/
$hostingerPublic = dirname(__DIR__, 2) . '/public_html';
if (is_dir($hostingerPublic)) {
    $app->usePublicPath($hostingerPublic);
}

return $app;
