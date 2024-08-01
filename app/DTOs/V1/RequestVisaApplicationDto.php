<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;

class RequestVisaApplicationDto {
    // Setter, Getter
    public function getOrderStayPeriod() : int {return $this->order_stay_period;}
    public function getOrderStayStatus() : string {return $this->order_stay_status;}

    // Creator
    function __construct(
        private readonly int $order_stay_period,
        private readonly string $order_stay_status
    ) {}

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return RequestVisaApplicationDto
     */
    public static function createFromRequest(Request $request) : RequestVisaApplicationDto {
        return new static(
            $request->input('order_stay_period'),
            $request->input('order_stay_status')
        );
    }
}
