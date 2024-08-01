<?php

namespace App\Http\JsonResponses\V2\Base;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class UserInfoResponse extends JsonResponse {
    function __construct(User $user) {
        parent::__construct(static::toArray($user));
    }

    /**
     * 현재 사용자 정보로 응답한다.
     * @param User $user
     * @return array
     * @OA\Schema (
     *     schema="user_info",
     *     title="회원정보",
     *     @OA\Property (
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property (
     *          property="user_types",
     *          type="array",
     *          @OA\Items (type="integer")
     *     ),
     *     @OA\Property (
     *          property="id_alias",
     *          type="string",
     *          description="암호화된 ID 별칭"
     *     ),
     *     @OA\Property (
     *          property="email",
     *          type="string",
     *          description="이메일 주소"
     *     ),
     *     @OA\Property (
     *          property="is_organization",
     *          type="boolean",
     *          description="단체회원 여부"
     *     ),
     *     @OA\Property (
     *          property="name",
     *          type="string",
     *          description="이름 (개인 또는 단체명)"
     *     ),
     *     @OA\Property (property="country", ref="#/components/schemas/country"),
     *     @OA\Property (
     *          property="photo",
     *          type="string",
     *          description="회원사진 경로"
     *     ),
     *     @OA\Property (
     *          property="cell_phone",
     *          type="string",
     *          description="전화번호"
     *     ),
     *     @OA\Property (
     *          property="address",
     *          type="string",
     *          description="주소"
     *     ),
     *     @OA\Property (
     *          property="family_name",
     *          type="string",
     *          description="성"
     *     ),
     *     @OA\Property (
     *          property="given_names",
     *          type="string",
     *          description="이름"
     *     ),
     *     @OA\Property (
     *          property="hanja_name",
     *          type="string",
     *          description="한자이름"
     *     ),
     *     @OA\Property (
     *          property="identity_no",
     *          type="string",
     *          description="신분증 번호"
     *     ),
     *     @OA\Property (
     *          property="sex",
     *          type="string",
     *          description="성별"
     *     ),
     *     @OA\Property (
     *          property="birthday",
     *          type="date",
     *          description="생년월일"
     *     ),
     *     @OA\Property (property="birth_country", ref="#/components/schemas/country"),
     *     @OA\Property (
     *          property="another_nationalities",
     *          type="array",
     *          @OA\Items(ref="#/components/schemas/country")
     *     ),
     *     @OA\Property (
     *          property="old_family_name",
     *          type="string",
     *          description="변경 전 성"
     *     ),
     *     @OA\Property (
     *          property="old_given_names",
     *          type="string",
     *          description="변경 전 이름"
     *     ),
     *     @OA\Property (
     *          property="registration_no",
     *          type="string",
     *          description="사업자등록 번호"
     *     ),
     *     @OA\Property (
     *          property="boss_name",
     *          type="string",
     *          description="대표자명"
     *     ),
     *     @OA\Property (
     *          property="manager_name",
     *          type="string",
     *          description="담당자 이름"
     *     ),
     *     @OA\Property (
     *          property="telephone",
     *          type="string",
     *          description="전화번호"
     *     ),
     *     @OA\Property (
     *          property="fax",
     *          type="string",
     *          description="팩스번호"
     *     ),
     *     @OA\Property (
     *          property="road_map",
     *          type="string",
     *          description="약도 이미지 경로"
     *     ),
     *     @OA\Property (
     *          property="longitude",
     *          type="number",
     *          format="double",
     *          description="사무실 위치 (경도)"
     *     ),
     *     @OA\Property (
     *          property="latitude",
     *          type="number",
     *          format="double",
     *          description="사무실 위치 (위도)"
     *     ),
     *     @OA\Property (
     *           property="organization",
     *           ref="#/components/schemas/simple_user_info",
     *           description="소속 기관/단체"
     *     )
     * )
     */
    public static function toArray(User $user) : array {
        $country = Country::findMe($user->country_id)?->toArray();
        $birth_country = Country::findMe($user->birth_country_id)?->toArray();
        $t_another_nationalities = $user->another_nationality_ids ?
            Country::find(json_decode($user->another_nationality_ids)) : null;
        if($t_another_nationalities) {
            $another_nationalities = [];
            foreach($t_another_nationalities as $c) $another_nationalities[] = $c->toArray();
        } else $another_nationalities = null;

        $access_token = access_token();
        $photo_url = $user->photo ?
            route('api.v2.user.show_photo', ['id' => $user->id, '_token' => $access_token])
            : null;
        $road_map_url = $user->road_map ?
            route('api.v2.user.show_road_map', ['id' => $user->id, '_token' => $access_token])
            : null;

       return [
            'id' => $user->id,
            'user_types' => $user->getTypes()->pluck('type')->toArray(),
            'id_alias' => $user->id_alias,
            'email' => $user->email,
            'is_organization' => $user->is_organization == 1,
            'name' => $user->name,
            'country' => $country,
            'photo' => $photo_url,
            'cell_phone' => $user->cell_phone,
            'address' => $user->address,
            'family_name' => $user->family_name,
            'given_names' => $user->given_names,
            'hanja_name' => $user->hanja_name,
            'identity_no' => $user->identity_no,
            'sex' => $user->sex,
            'birthday' => $user->birthday,
            'birth_country' => $birth_country,
            'another_nationalities' => $another_nationalities,
            'old_family_name' => $user->old_family_name,
            'old_given_names' => $user->old_given_names,
            'registration_no' => $user->registration_no,
            'boss_name' => $user->boss_name,
            'manager_name' => $user->manager_name,
            'telephone' => $user->telephone,
            'fax' => $user->fax,
            'road_map' => $road_map_url,
            'longitude' => $user->longitude,
            'latitude' => $user->latitude,
            'organization' => User::findMe($user->management_org_id)?->toSimpleArray()
        ];
    }
}
