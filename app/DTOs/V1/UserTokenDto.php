<?php

namespace App\DTOs\V1;

use App\Models\AccessToken;
use App\Models\User;

class UserTokenDto {
    function __construct(
        private readonly User $user,
        private readonly AccessToken $token,
        private readonly int $response_code = 200
    ) {}

    public function getUser() : ?User {return $this->user;}
    public function getApiToken() : ?string {return $this->user->api_token;}
    public function getAccessToken() : ?AccessToken {return $this->token;}
    public function getAccessTokenString() : ?string {return $this->token?->token;}
    public function getHttpResponseCode() : int {return $this->response_code;}
}
