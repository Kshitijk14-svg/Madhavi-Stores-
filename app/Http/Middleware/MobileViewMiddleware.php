<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileViewMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->header('User-Agent');

        // The admin panel has its own responsive desktop layout. Without this guard
        // the prepended mobile path makes admin's `@extends('layouts.app')` resolve
        // to the mobile *storefront* shell (bottom tab bar, cart, etc.), wrapping
        // the dashboard in customer chrome. Admin must always use the real layout.
        if ($request->is('admin') || $request->is('admin/*')) {
            return $next($request);
        }

        if ($userAgent && preg_match('/Mobile|Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile/i', $userAgent)) {
            $mobileViewsPath = resource_path('views/mobile');
            if (is_dir($mobileViewsPath)) {
                \Illuminate\Support\Facades\View::getFinder()->prependLocation($mobileViewsPath);
            }
        }

        return $next($request);
    }
}
