<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SyncPincodeSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $cookiePin = $request->cookie('user_pincode');
        $sessionPin = session('user_pincode');

        if ($cookiePin) {
            if ($sessionPin !== $cookiePin) {
                session(['user_pincode' => $cookiePin]);
            }
        } else {
            if ($sessionPin) {
                session()->forget('user_pincode');
            }
        }

        return $next($request);
    }
}
