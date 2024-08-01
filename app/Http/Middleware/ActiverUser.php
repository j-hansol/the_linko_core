<?php

namespace App\Http\Middleware;

use App\Http\JsonResponses\Common\Message;
use Closure;
use Illuminate\Http\Request;

class ActiverUser
{
    /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return Message|mixed|void
     */
    public function handle(Request $request, Closure $next, ?string $guard = null) {
        $user = current_user();
        if($user && $user->active == 1) return $next($request);
        else {
            if($guard == 'api' ) return new Message(401);
            else abort(401);
        }
    }
}
