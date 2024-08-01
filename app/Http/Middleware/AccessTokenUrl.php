<?php

namespace App\Http\Middleware;

use App\Http\JsonResponses\Common\Message;
use App\Models\AccessToken as AccessTokenModel;
use App\Models\User;
use App\Traits\Common\ValidationAccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenUrl {
    use ValidationAccessToken;
    /**
     * Handle an incoming request.
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handle(Request $request, Closure $next): Response {
        $token = request()->get('_token');
        if(!$token) new Message(401);
        list($id_alias, $etc) = explode('-', $token);
        $user = User::findByIdAlias($id_alias);
        if(!$user) return new Message(401);
        else {
            AccessTokenModel::deleteInvalidAccessTokenForUser($user);
            if($this->isValidAToken($token, true)) {
                Auth::login($user);
                return $next($request);
            }
            else return new Message(401);
        }
    }
}
