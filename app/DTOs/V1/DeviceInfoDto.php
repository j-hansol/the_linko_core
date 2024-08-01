<?php

namespace App\DTOs\V1;

use App\Lib\DeviceType;
use Illuminate\Http\Request;

class DeviceInfoDto {
    // 송성
    private ?DeviceType $device_type;
    private ?string $device_name;
    private ?string $uuid;
    private ?string $fcm_token;

    // Setter, Getter
    public function setDeviceType(?DeviceType $type) : void {$this->device_type = $type;}
    public function getDeviceType() : ?DeviceType {return $this->device_type;}
    public function setDeviceName(?string $name) : void {$this->device_name = $name;}
    public function getDeviceName() : ?string {return $this->device_name;}
    public function setUUID(?string $uuid) : void {$this->uuid = $uuid;}
    public function getUUID() : ?string {return $this->uuid;}
    public function setFcmToken(?string $token) : void {$this->fcm_token = $token;}
    public function getFcmToken() : ?string {return $this->fcm_token;}

    // Creator

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return DeviceInfoDto
     */
    public static function createFromRequest(Request $request, ?DeviceType $type = null) : DeviceInfoDto {
        $dto = new static();
        if(!$type) $dto->setDeviceType($request->enum('device_type', DeviceType::class));
        else $dto->setDeviceType($type);
        $dto->setDeviceName($request->input('device_name'));
        $dto->setUUID($request->input('uuid'));
        $dto->setFcmToken($request->input('fcm'));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'name' => $this->device_name,
            'uuid' => $this->uuid,
            'fcm_token' => $this->fcm_token
        ];
    }
}
