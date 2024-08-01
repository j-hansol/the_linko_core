<?php

namespace App\DTOs\V1;

use App\Lib\CertificationTokenFunction;
use App\Lib\CryptDataB64 as CryptData;
use Illuminate\Http\Request;

class RequestCertificationTokenDto {
    // 생성자
    function __construct(
        private readonly string $email,
        private readonly CertificationTokenFunction $function
    ) {}

    // Getter
    public function getEmail() : string {return $this->email;}
    public function getFunction() : CertificationTokenFunction {return $this->function;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return RequestCertificationTokenDto
     */
    public static function createFromRequest(Request $request) : RequestCertificationTokenDto {
        return new static(
            CryptData::decrypt($request->input('email'), 'email'),
            $request->enum('certification_function', CertificationTokenFunction::class)
        );
    }
}
