<?php

namespace App\DTOs\V1;

use App\Lib\DeviceType;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use Illuminate\Http\Request;

class AutoLoginDto {
    // 속성
    private ?string $device_name;
    private ?string $uuid;
    private ?string $fcm_token;

    // 생성자
    function __construct(
        private readonly DeviceType $device_type,
    ) {}

    // Getter
    public function getDeviceType() : DeviceType {return $this->device_type;}
    /**
     * 단말기 이름을 설정한다.
     * @param string|null $device_name
     * @return void
     * @throws HttpException
     */
    public function setDeviceName(?string $device_name) : void {
        $this->device_name = $device_name;
    }
    public function getDeviceName() : ?string {return $this->device_name;}

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

    /**
     * 파이어베이스 메시지 토콘을 설정한다.
     * @param string|null $token
     * @return void
     */
    public function setFCMToken(?string $token) : void {
        $this->fcm_token = $token;
    }
    public function getFCMToken() : ?string {return $this->fcm_token;}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return AutoLoginDto
     * @throws HttpException
     */
    public static function createFromRequest(Request $request) : AutoLoginDto {
        $dto = new static(
            $request->enum('device_type', DeviceType::class)
        );
        $dto->setDeviceName($request->input('device_name'));
        $dto->setUUID($request->input('uuid'));
        $dto->setFCMToken($request->input('fcm'));
        return $dto;
    }
}
