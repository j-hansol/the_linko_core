<?php

namespace App\DTOs\V2;

use App\Lib\LoginMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthInfoDto {
    // 속성
    private ?string $password;
    private ?string $hashed_password;

    // 생성자
    function __construct(
        private readonly LoginMethod $login_method,
    ) {}

    // Setter, Getter
    public function getLoginMethod() : LoginMethod {return $this->login_method;}
    public function setPassword(?string $password) : void {
        $this->password = $password;
        $this->hashed_password = $password ? Hash::make($this->password) : null;
    }
    public function getPassword() : ?string {return $this->password;}
    public function getHashedPassword() : ?string {return $this->hashed_password;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 체를 생성한다.
     * @param Request $request
     * @return AuthInfoDto
     */
    public static function createFromRequest(Request $request) : AuthInfoDto {
        $dto = new static($request->enum('login_method', LoginMethod::class));
        $dto->setPassword($request->input('password'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'password' => $this->hashed_password,
            'login_method' => $this->login_method->value,
        ];
    }
}
