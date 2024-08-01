<?php

namespace App\Traits\Common;

use App\Models\AccessToken as AccessTokenModel;

trait ValidationAccessToken {
    public function isValidAToken(string $token_string, bool $active = false) : bool {
        $token = AccessTokenModel::find($token_string);
        if(!$token) return false;
        if($active && $token->active != 1) return false;
        $token->updateAccessTime();
        return true;
    }
}
