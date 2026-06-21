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
        ]);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->web(append: [
            \App\Http\Middleware\MobileViewMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// On Hostinger: madhavi-app/ and public_html/ are siblings inside ~/domains/madhavistores.in/
// dirname(__DIR__, 2) = ~/domains/madhavistores.in — so public_html is right next to madhavi-app/
$hostingerPublic = dirname(__DIR__, 2) . '/public_html';
if (is_dir($hostingerPublic)) {
    $app->usePublicPath($hostingerPublic);
}

return $app;
