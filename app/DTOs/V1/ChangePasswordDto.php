<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangePasswordDto {
    // 속성
    private string $hashed_password;

    // 생성자
    function __construct(
        private readonly string $current_password,
        private readonly string $password
    ) {$this->hashed_password = Hash::make($this->password);}

    // Getter
    public function getCurrentPassword() : string {return $this->current_password;}
    public function getPassword() : string {return $this->password;}
    public function getHashedPassword() : string {return $this->hashed_password;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return ChangePasswordDto
     */
    public static function createFromRequest(Request $request) : ChangePasswordDto {
        return new static(
            CryptData::decrypt($request->input('current_password'), 'current_password'),
            CryptData::decrypt($request->input('password'), 'password')
        );
    }
}
