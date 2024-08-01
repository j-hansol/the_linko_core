<?php

namespace App\Http\JsonResponses\V2\Base;
use App\DTOs\V2\UserTokenDto;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="login_token",
 *     title="로그인 결과 토큰",
 *     @OA\Property (
 *          property="api_token",
 *          type="string",
 *          description="API 토큰"
 *     ),
 *     @OA\Property (
 *          property="access_token",
 *          type="string",
 *          description="엑세스 토큰"
 *     ),
 * )
 */
class TokenResponse extends JsonResponse {
    function __construct(UserTokenDto $dto) {
        parent::__construct([
            'message' => __('api.r' . $dto->getHttpResponseCode()),
            'api_token' => $dto->getApiToken(),
            'access_token' => $dto->getAccessTokenString()
        ], $dto->getHttpResponseCode());
    }
}
