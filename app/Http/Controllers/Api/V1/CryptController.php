<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\JsonResponses\Common\Data;
use App\Http\JsonResponses\Common\ErrorMessage;
use App\Http\JsonResponses\Common\Message;
use App\Http\JsonResponses\Common\NoAcceptableMessage;
use App\Lib\CryptData;
use App\Lib\CryptDataB64;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="crypt",
 *     description="로그인 등에서 민감한 정보를 송수신할 때 암호화하도록 합니다.
암호화 알고리즘은 'AES-256-CBC'을 이용합니다.
암호화 방법은 아래와 같습니다.
1. 키는 암호화 및 복호화 단말기간 고정된(약속된) 키를 이용한다.
2. 보조키(iv)를 난수발생기를 이용하여 문자열을 해당 길이만큼 생성한다.
3. 키와 보조키를 이용하여 데이터를 암호화한다.
4. 보조키 + 암호화된 데이터를 연결하여 해시함수(md5)를 이용하여 해시갑을 계산한다.
5. 해시갑 + 보조키 + 암호화된 데이터를 연결하여 엔코딩(base64)하여 리턴한다."
 * )
 */
class CryptController extends Controller {
    /**
     * 전달된 문자열을 암호화한다.
     * @param Request $request
     * @return JsonResponse
     * @OA\Schema (
     *     schema="encrypted_text",
     *     title="암호화 텍스트",
     *     @OA\Property (
     *          property="encrypted_text",
     *          type="string",
     *          description="암호화 텍스트"
     *     )
     * )
     * @OA\Post (
     *      path="/crypt/encrypt",
     *      tags={"crypt"},
     *      @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (allOf={@OA\Schema(ref="#/components/schemas/text")})
     *          )
     *      ),
     *      @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
 *                      @OA\Schema (ref="#/components/schemas/encrypted_text")
     *              }
     *          )
     *      ),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function encrypt( Request $request ) : JsonResponse {
        try {
            $validator = Validator::make( $request->input(), [
                'text' => ['required', 'string']
            ]);

            if( $validator->fails() ) return new Message(400);
            return new Data(['encrypted_text' => CryptData::encrypt( $request->input('text'))]);
        } catch ( \Exception $e ) {
            return new Message(500);
        }
    }

    /**
     * 전달된 암호문을 복호화한다.
     * @param Request $request
     * @return JsonResponse
     * @OA\Schema (
     *     schema="decrypted_text",
     *     title="복호화 텍스트",
     *     @OA\Property (
     *          property="decrypted_text",
     *          type="string",
     *          description="복호화된 텍스트"
     *     )
     * )
     * @OA\Post (
     *      path="/crypt/decrypt",
     *      tags={"crypt"},
     *      @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (allOf={@OA\Schema(ref="#/components/schemas/text")})
     *          )
     *      ),
     *      @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/decrypted_text")
     *              }
     *          )
     *      ),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function decrypt( Request $request ) : JsonResponse {
        try {
            $validator = Validator::make( $request->input(), [
                'text' => ['required', 'string']
            ]);

            if( $validator->fails() ) return new Message(400);
            $text = CryptData::decrypt( $request->input('text') );
            if( !empty( $text ) ) return new Data(['decrypted_text' => $text]);
            else return new ErrorMessage(['Invalid encrypted data.'], 406);
        } catch ( \Exception $e ) {
            return new Message(500);
        }
    }

    /**
     * 전달된 문자열을 암호화한다. (일부 Base64 활용)
     * @param Request $request
     * @return JsonResponse
     * @OA\Post (
     *      path="/crypt/encryptB64",
     *      tags={"crypt"},
     *      @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (allOf={@OA\Schema(ref="#/components/schemas/text")})
     *          )
     *      ),
     *      @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                      @OA\Schema (ref="#/components/schemas/encrypted_text")
     *              }
     *          )
     *      ),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function encryptB64( Request $request ) : JsonResponse {
        try {
            $validator = Validator::make( $request->input(), [
                'text' => ['required', 'string']
            ]);

            if( $validator->fails() ) return new Message(400);
            return new Data(['encrypted_text' => CryptDataB64::encrypt( $request->input('text'))]);
        } catch ( \Exception $e ) {
            return new Message(500);
        }
    }

    /**
     * 전달된 암호문을 복호화한다. (일부 Base64 활용)
     * @param Request $request
     * @return JsonResponse
     * @OA\Post (
     *      path="/crypt/decryptB64",
     *      tags={"crypt"},
     *      @OA\RequestBody (
     *          @OA\MediaType (
     *              mediaType="multipart/form-data",
     *              @OA\Schema (allOf={@OA\Schema(ref="#/components/schemas/text")})
     *          )
     *      ),
     *      @OA\Response (
     *          response=200,
     *          description="요청 성공",
     *          @OA\JsonContent (
     *              allOf={
     *                  @OA\Schema (ref="#/components/schemas/api_message"),
     *                  @OA\Schema (ref="#/components/schemas/decrypted_text")
     *              }
     *          )
     *      ),
     *      @OA\Response (response=400, ref="#/components/responses/400"),
     *      @OA\Response (response=500, ref="#/components/responses/500")
     * )
     */
    public function decryptB64( Request $request ) : JsonResponse {
        try {
            $validator = Validator::make( $request->input(), [
                'text' => ['required', 'string']
            ]);

            if( $validator->fails() ) return new Message(400);
            $text = CryptDataB64::decrypt( $request->input('text') );
            if( !empty( $text ) ) return new Data(['decrypted_text' => $text]);
            else return new ErrorMessage([__('errors.crypt.invalid_encrypted_data')], 406);
        } catch ( \Exception $e ) {
            return new Message(500);
        }
    }
}
