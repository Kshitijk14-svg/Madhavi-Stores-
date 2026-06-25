<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToDesktop
{
    /**
     * Block routes that have no usable mobile experience (e.g. the drag-and-drop
     * Design Manager). Mobile user agents are bounced to the admin dashboard
     * rather than served the desktop-only view, so the page is unreachable on
     * phones even via a direct link. Uses the same UA test as MobileViewMiddleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->header('User-Agent');

        if ($userAgent && preg_match('/Mobile|Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile/i', $userAgent)) {
            $message = 'The Design Manager is only available on desktop.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return redirect()->route('admin.dashboard')->with('error', $message);
        }

        return $next($request);
    }
}
