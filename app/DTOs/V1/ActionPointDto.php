<?php

namespace App\DTOs\V1;

use App\Lib\ActionPointType;
use Illuminate\Http\Request;

class ActionPointDto {
    // 생성자
    function __construct(
        private readonly ActionPointType $type,
        private readonly string $name,
        private readonly string $address,
        private readonly float $longitude,
        private readonly float $latitude,
        private readonly float $radius
    ) {}

    // Getter
    public function getType() : ActionPointType {return $this->type;}
    public function getName() : string {return $this->name;}
    public function getAddress() : string {return $this->address;}
    public function getLongitude() : float {return $this->longitude;}
    public function getLatitude() : float {return $this->latitude;}
    public function getRadius() : float {return $this->radius;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return $this
     */
    public static function createFromRequest(Request $request) : ActionPointDto {
        return new static(
            $request->enum('type', ActionPointType::class),
            $request->input('name'),
            $request->input('address'),
            $request->float('longitude'),
            $request->float('latitude'),
            $request->float('radius')
        );
    }

    // for model
    public function toArray() : array {
        return [
            'type' => $this->type->value,
            'name' => $this->name,
            'address' => $this->address,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'radius' => $this->radius
        ];
    }
}
