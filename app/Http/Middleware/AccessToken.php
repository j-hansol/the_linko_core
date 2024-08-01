<?php

namespace App\Http\Middleware;

use App\Http\JsonResponses\Common\Message;
use App\Models\AccessToken as AccessTokenModel;
use App\Traits\Common\ValidationAccessToken;
use Closure;
use Illuminate\Http\Request;

class AccessToken {
    use ValidationAccessToken;
    /**
     * 기간이 만료된 엑세스 토큰을 삭제하고, 해더를 통해 전달된 토큰문자열로 토큰정보를 가져와 유효성을 검사한다.
     *
     * @param Request $request
     * @param Closure $next
     * @param $active
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next, ?string $active = null) {
        $user = current_user();
        AccessTokenModel::deleteInvalidAccessTokenForUser($user);

        if($this->isValidAToken(access_token(), $active == 'active')) return $next($request);
        else return new Message(401);
    }
}
