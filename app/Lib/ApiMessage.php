<?php

namespace App\Lib;

use Illuminate\Http\JsonResponse;

/**
 * 현재 이 클레스는 사용을 권장하지 않습니다.
 */
class ApiMessage {
    /**
     * 응듭 메시지릉 리턴한다.
     * @param int $code
     * @return JsonResponse
     */
    public static function message( int $code = 200 ) : JsonResponse {
        return response()->json([
            'message' => __('api.r' . $code)
        ], $code);
    }

    /**
     * 응답 메시지에 데이터를 포함하여 리턴한다.
     * @param array|null $data
     * @param int $code
     * @return JsonResponse
     */
    public static function data( ?array $data, int $code = 200 ) : JsonResponse {
        $message = ['message' => __('api.r' . $code)];
        if( $data ) $message += $data;
        return response()->json($message, $code);
    }

    /**
     * 잘 못된 요청 시 관련 에러를 함께 리턴한다.
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function validationErrorMessage( mixed $errors ) : JsonResponse {
        if( !is_array( $errors ) ) $errors = ['code' => $errors ];
        foreach( $errors as $name => $value ) {
            if( is_array( $value ) ) $errors[$name] = reset( $value );
        }
        return self::data(['errors' => $errors], 400);
    }
}
