<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class PreSaveWorkerInfo extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id', 'management_org_id', 'email', 'cell_phone', 'address', 'family_name', 'given_names', 'hanja_name',
        'identity_no', 'sex', 'birthday', 'old_family_name', 'old_given_names'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 회원계정 생성 가능 여부를 판단한다.
     * @return bool
     */
    public function isAccountAble() : bool {
        $manager = User::findMe($this->management_org_id);
        if(!$manager) return false;

        $validator = Validator::make($this->getOriginal(), [
            'email' => ['required', 'email', 'unique:users,email'],
            'cell_phone' => ['required'],
            'address' => ['required'],
            'family_name' => ['required'],
            'given_names' => ['required'],
            'identity_no' => ['required'],
            'sex' => ['required', 'in:M,F'],
            'birthday' => ['required', 'date']
        ]);

        return $validator->fails();
    }

    /**
     * 저장된 정보를 배열로 리턴한다.
     * @return array
     * @OA\Schema (
     *     schema="pre_save_worker_info",
     *     title="임시 저장된 근로자 정보",
     *     @OA\Property (
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property (
     *          property="useer_id",
     *          type="integer",
     *          description="회원계정 계정 일련번호"
     *     ),
     *     @OA\Property (
     *          property="management_org_id",
     *          type="integer",
     *          description="관리기관 계정 일련번호"
     *     ),
     *     @OA\Property (
     *          property="email",
     *          type="string",
     *          description="암호화된 이메일 주소"
     *     ),
     *     @OA\Property (
     *          property="cell_phone",
     *          type="string",
     *          description="암호화된 전화번호"
     *     ),
     *     @OA\Property (
     *          property="address",
     *          type="string",
     *          description="암호화된 주소"
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
     *          description="암호화된 신분증 번호"
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
     *          property="created_at",
     *          type="string",
     *          format="date-time",
     *          description="생성일시"
     *     ),
     *     @OA\Property (
     *          property="updated_at",
     *          type="string",
     *          format="date-time",
     *          description="수정일시"
     *     )
     * )
     */
    public function toInfoArray() : array {
        return $this->toArray();
    }
}
