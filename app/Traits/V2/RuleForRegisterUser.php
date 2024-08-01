<?php

namespace App\Traits\V2;

use App\Lib\DeviceType;
use App\Lib\LoginMethod;
use App\Lib\MemberType;
use App\Rules\ExistsValues;
use App\Rules\RequiredOrNull;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Annotations as OA;

trait RuleForRegisterUser {
    /**
     * 회원가입 시 필요한 기본 데이터 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="join_common",
     *     title="회원가입 공통항목",
     *          @OA\Property(
     *           property="id_alias",
     *           type="string",
     *           description="ID (id 별칭)"
     *      ),
     *     @OA\Property(
     *          property="email",
     *          type="string",
     *          description="이메일 주소"
     *     ),
     *     @OA\Property(
     *          property="country_id",
     *          type="integer",
     *          description="국가(국적) 일련번호"
     *     ),
     *     @OA\Property(
     *          property="cell_phone",
     *          type="string",
     *          description="휴대전화 번호"
     *     ),
     *     @OA\Property(
     *          property="address",
     *          type="string",
     *          description="주소"
     *     ),
     *     required={"id_alias", "email", "country_id", "cell_phone", "address"}
     * )
     */
    public function getCommonRule() : array {
        return [
            'id_alias' => ['required', 'unique:users,id_alias'],
            'email' => ['required', 'email', 'unique:users,email'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'cell_phone' => ['required'],
            'address' => ['required'],
        ];
    }

    /**
     * 수정 가능한 공통 프로필 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="editable_common_profile",
     *     title="수정가능 공통 프로필",
     *     @OA\Property (
     *          property="email",
     *          type="string",
     *          description="전자우편 주소, 다른 회원가 중복 불가"
     *     ),
     *     @OA\Property (
     *          property="country_id",
     *          type="integer",
     *          description="소속 국가"
     *     ),
     *     @OA\Property (
     *          property="cell_phone",
     *          type="string",
     *          description="휴대전화 번호"
     *     ),
     *     @OA\Property (
     *          property="address",
     *          type="string",
     *          description="주소"
     *     ),
     *     required={"email", "cell_phone", "address"}
     * )
     */
    public function getEditableCommonRule() : array {
        $user = current_user();
        return [
            'email' => ['required', 'email', Rule::unique('users', 'id')->ignore($user->id)],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'cell_phone' => ['required'],
            'address' => ['required'],
        ];
    }

    /**
     * 로그인 방법 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="join_login_method",
     *     title="로그인 방법관련 항목",
     *     @OA\Property(
     *          property="login_method",
     *          allOf={@OA\Schema(ref="#/components/schemas/LoginMethod")},
     *          description="로그인 방법"
     *     ),
     *     @OA\Property(
     *          property="auth_provider",
     *          type="string",
     *          description="SNS 로그인 지원 서비스명 (현재 facebook만 지원)"
     *     ),
     *     @OA\Property(
     *          property="auth_provider_identifier",
     *          type="string",
     *          description="SNS 로그인 후 계정 일련번호"
     *     ),
     *     @OA\Property(
     *          property="password",
     *          type="string",
     *          description="접속 비밀번호 (로그인 방법이 비밀번호(LOGIN_METHOD_PASSWORD)인 경우 필수, 암호화 필요"
     *     ),
     *     required={"login_method"}
     * )
     */
    public function getLoginMethodRule() : array {
        return ['login_method' => ['required', new Enum(LoginMethod::class)]];
    }

    /**
     * 개인회원 유형 유형성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="person_type",
     *     title="단체회원 유형",
     *     @OA\Property (
     *          property="type",
     *          type="string",
     *          description="개인회원 유형",
     *          enum={"20", "100", "110", "170", "190", "900", "910"}
     *     ),
     *     required={"type"}
     * )
     */
    public function getPersonUserTypeRule() : array {
        return ['type' => ['required', new Enum(MemberType::class), 'in:20,100,110,170,190,900,910']];
    }

    /**
     * 단체회원 유형 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="organization_type",
     *     title="단체회원 유형",
     *     @OA\Property (
     *          property="type",
     *          type="string",
     *          description="단체회원 유형",
     *          enum={"30", "40", "60", "70", "80", "90", "130", "140", "150", "160", "180"}
     *     ),
     *     required={"type"}
     * )
     */
    public function getOrganizationUserTypeRule() : array {
        return ['type' => ['required', new Enum(MemberType::class), 'in:30,40,60,70,80,90,130,140,150,160,180']];
    }

    /**
     * 개인회원 정보 유형성 검사 규칙을 리턴한다.
     * @return array
     *
     */
    public function getPersonInfoRule() : array {
        return [
            'family_name' => ['required'],
            'given_names' => ['required'],
            'identity_no' => ['required'],
            'sex' => ['required', 'in:M,F'],
            'birthday' => ['required', 'date'],
            'birth_country_id' => ['required', 'integer', 'exists:countries,id'],
            'another_nationality_ids' => ['nullable', new ExistsValues('countries', 'id')],
        ];
    }

    public function getOrganizationInfoRule() : array {
        return [
            'name' => ['required', 'string'],
            'registration_no' => ['required', 'string'],
            'boss_name' => ['required'],
            'manager_name' => ['required'],
        ];
    }

    /**
     * 회원 가입 시 비밀번호 입력 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema(
     *     schema="join_password",
     *     title="로그인 비밀번호 항목",
     *     @OA\Property(
     *          property="password",
     *          type="string",
     *          description="접속 비밀번호 (로그인 방법이 비밀번호(LOGIN_METHOD_PASSWORD)인 경우 필수, 암호화 필요"
     *     )
     * )
     */
    public function getPasswordRule() : array {
        return [
            'password' => [(new RequiredOrNull())->required($this->input('login_method') == 10)],
        ];
    }

    /**
     * 소속 기관 입력 유효성 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="management_org_id",
     *     title="소속 기관 일련번호",
     *     @OA\Property (
     *          property="management_org_id",
     *          type="integer",
     *          description="소속 기관 일년번호"
     *     ),
     * )
     */
    public function getManagementOrgRule() : array {
        return ['management_org_id' => ['nullable', 'integer', 'exists:users,id']];
    }

    /**
     * 단말기 정보 유효성 검사 규칙을 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="device",
     *     title="단말기",
     *     @OA\Property (
     *          property="device_type",
     *          type="integer",
     *          allOf={@OA\Schema(ref="#/components/schemas/DeviceType")},
     *          description="단말기 유형"
     *     ),
     *     @OA\Property (
     *          property="uuid",
     *          type="string",
     *          description="단말기 식별 UUID (단말기 종류가 모바일인 경우 필수)"
     *     ),
     *     @OA\Property (
     *          property="fcm",
     *          type="string",
     *          description="FCM 토큰 (선택입력)"
     *     ),
     *     required={"device_type"}
     * )
     */
    public function getDeviceInfoRule() : array {
        return [
            'device_type' => ['required', new Enum(DeviceType::class)],
            'uuid' => [$this->input('device_type') == DeviceType::TYPE_MOBILE->value ? 'required' : 'nullable', 'string', 'unique:devices,uuid'],
            'fcm' => ['nullable', 'string']
        ];
    }
}
