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
        
        if ($userAgent && preg_match('/Mobile|Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile/i', $userAgent)) {
            $mobileViewsPath = resource_path('views/mobile');
            if (is_dir($mobileViewsPath)) {
                \Illuminate\Support\Facades\View::getFinder()->prependLocation($mobileViewsPath);
            }
        }

        return $next($request);
    }
}
