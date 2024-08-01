<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use OpenApi\Annotations as OA;

/**
 * @OA\Info (
 *      version="1.0",
 *      title="더 링코 코어(The Linko Core) Api 서비스",
 *      description="본 API 서비스는 자체 시비스인 차비스 를 위해 개발되었습니다.",
 *      @OA\Contact(
 *          email="sh.jang@sohocode.kr"
 *      ),
 *      @OA\License(
 *          name="Private"
 *      )
 * )
 * @OA\ExternalDocumentation(
 *     description="참조 내용",
 *     url="/references"
 * )
 * @OA\Server(
 *      url="https://public.the-linko-core.wd/api/v1",
 *      description="백엔드 로컬 개발환경 전용"
 * )
 * @OA\Server(
 *      url="https://tlk.sohocode.kr/api/v1",
 *      description="프론트엔드 테스트서버"
 * )
 *
 * @OA\securityScheme(
 *      securityScheme="BearerAuth",
 *      type="http",
 *      scheme="Bearer",
 * )
 *
 * @OA\securityScheme(
 *      securityScheme="AccessTokenAuth",
 *      in="header",
 *      type="apiKey",
 *      name="X-ACCESS-TOKEN",
 * )
 */
class BaseController extends Controller {
    /**
     * @var mixed
     *
     * =====================================================
     * 쿼리 파라미터
     * -----------------------------------------------------
     * @OA\Parameter (
     *     name="id_alias",
     *     in="path",
     *     required=true,
     *     description="회원코드",
     *     @OA\Schema (type="string")
     * )
     * @OA\Parameter (
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="일련번호",
     *     @OA\Schema (type="integer")
     * )
     * @OA\Parameter (
     *     name="attorney_id",
     *     in="path",
     *     required=true,
     *     description="행정사 계정 일련번호",
     *     @OA\Schema (type="integer")
     * )
     * @OA\Parameter (
     *     name="_token",
     *     in="query",
     *     required=true,
     *     description="인증토큰",
     *     @OA\Schema (type="string")
     * )
     * @OA\Parameter (
     *     name="active_range",
     *     in="query",
     *     required=false,
     *     description="활성화 필터 범위 (all:모두, 0:활성화되지 않음, 1:활성화됨)",
     *     @OA\Schema (type="string", enum={"all","0","1"})
     * )
     * @OA\Parameter (
     *     name="country_id",
     *     in="query",
     *     required=false,
     *     description="국가정보 일련번호",
     *     @OA\Schema (type="integer")
     * )
     * @OA\Parameter (
     *     name="visa_id",
     *     in="query",
     *     required=false,
     *     description="참고 대상 비자 신청 정보 일련번호",
     *     @OA\Schema (type="integer")
     * )
     *
     * =====================================================
     * 파일관련 스키마
     * -----------------------------------------------------
     * @OA\Schema (
     *     schema="image",
     *     title="이미지",
     *     @OA\Property (
     *          property="image",
     *          type="string",
     *          format="binary",
     *          description="이미지"
     *     ),
     *     required={"image"}
     * )
     * @OA\Schema (
     *     schema="file",
     *     title="문서",
     *     @OA\Property (
     *          property="file",
     *          type="string",
     *          format="binary",
     *          description="파일"
     *     ),
     *     required={"image"}
     * )
     *
     * =====================================================
     *  인증관련 스키마
     *  -----------------------------------------------------
     * @OA\Schema (
     *       schema="email",
     *       title="이메일",
     *       @OA\Property (
     *            property="email",
     *            type="string",
     *            description="이메일 주소, 암호화 필요"
     *       ),
     *       required={"email"}
     * )
     */
    private mixed $request_param;

    /**
     * @var mixed
     *
     * =====================================================
     * 기본 스키마
     * -----------------------------------------------------
     * @OA\Schema (
     *     schema="text",
     *     title="텍스트 입력",
     *     @OA\Property (
     *          property="text",
     *          type="string",
     *          description="입력 텍스트"
     *     )
     * )
     * @OA\Schema(
     *     schema="model_id",
     *     title="모델 일련번호",
     *     @OA\Property (
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     )
     * )
     * @OA\Schema(
     *     schema="model_timestamps",
     *     title="모델 타임스템프",
     *     @OA\Property (
     *          property="created_at",
     *          type="string",
     *          type="date-time",
     *          description="생성일시"
     *     ),
     *     @OA\Property (
     *          property="updated_at",
     *          type="string",
     *          type="date-time",
     *          description="수정일시"
     *     )
     * )
     * @OA\Schema (
     *     schema="info",
     *     title="정보",
     *     @OA\Property (
     *          property="info",
     *          type="object",
     *          description="정보"
     *     )
     * )
     * @OA\Schema(
     *     schema="user_types",
     *     description="이용가능 회원 유형",
     *     @OA\Property (
     *          property="types",
     *          type="array",
     *          @OA\Items (type="integer",ref="#/components/schemas/MemberType"),
     *          description="회원 유형"
     *     ),
     *     required={"types"}
     * )
     * @OA\Schema (
     *     schema="ids",
     *     title="일련번호 목록",
     *     @OA\Property(
     *         property="ids",
     *         type="array",
     *         description="정보 일련번호 (컴파로 구분하여 복수 입력 가능)",
     *         @OA\Items(type="integer")
     *     )
     * )
     */
    private mixed $response_data;
}
