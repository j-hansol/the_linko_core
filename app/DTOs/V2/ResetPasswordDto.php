<?php

namespace App\DTOs\V2;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordDto {
    // 속성
    private string $hashed_password;

    // 생성자
    function __construct(
        private readonly string $email,
        private readonly string $token,
        private readonly string $password
    ) {$this->hashed_password = Hash::make($this->password);}

    // Getter
    public function getEmail() : string {return $this->email;}
    public function getToken() : string {return $this->token;}
    public function getPassword() : string {return $this->password;}
    public function getHashedPassword() : string {return $this->hashed_password;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return ResetPasswordDto
     */
    public static function createFromRequest(Request $request) : ResetPasswordDto {
        return new static(
            $request->input('email'),
            $request->input('token'),
            $request->input('password')
        );
    }
}
