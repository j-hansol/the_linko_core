<?php

namespace App\Http\JsonResponses\Common;
use Illuminate\Http\JsonResponse;

class Data extends JsonResponse {
    function __construct(array $data, ?int $code = 200) {
        if($code > 0) parent::__construct(['message' => __('api.r' . $code)] + $data, $code);
        else parent::__construct(['message' => __('api.r404')], 404);
    }
}
