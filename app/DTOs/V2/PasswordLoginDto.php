<?php

namespace App\DTOs\V2;

use App\Lib\CryptDataB64 as CryptData;
use App\Lib\DeviceType;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Illuminate\Http\Request;

class PasswordLoginDto {
    // 속성
    private ?string $uuid;
    private ?string $fcm_token;

    // 생성자
    function __construct(
        private readonly string $id_alias,
        private readonly string $password,
        private readonly DeviceType $device_type,
    ) {}

    // Getter
    public function getIdAlias() : string {return $this->id_alias;}
    public function getPassword() : string {return $this->password;}
    public function getDeviceType() : DeviceType {return $this->device_type;}

    /**
     * 단말기 UUID를 설정한다.
     * @param string|null $uuid
     * @return void
     * @throws HttpErrorsException
     */
    public function setUUID(?string $uuid) : void {
        if($this->device_type == DeviceType::TYPE_MOBILE && !$uuid)
            throw HttpErrorsException::getInstance([__('errors.auth.required_uuid')], 400);
        $this->uuid = $uuid;
    }
    public function getUUID() : ?string {return $this->uuid;}
    public function setFCMToken(?string $token) : void {$this->fcm_token = $token;}
    public function getFCMToken() : ?string {return $this->fcm_token;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return PasswordLoginDto
     * @throws HttpErrorsException
     */
    public static function createFromRequest(Request $request) : PasswordLoginDto {
        $dto = new static(
            $request->input('id_alias'),
            $request->input('password'),
            $request->enum('device_type', DeviceType::class)
        );
        $dto->setUUID($request->input('uuid'));
        $dto->setFCMToken($request->input('fcm'));
        return $dto;
    }
}
