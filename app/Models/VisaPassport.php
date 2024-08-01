<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class VisaPassport extends Model
{
    use HasFactory;

    protected $fillable = [
        'visa_application_id', 'user_id', 'passport_type', 'other_type_detail', 'passport_no', 'passport_country_id',
        'issue_place', 'issue_date', 'text_issue_date', 'expire_date', 'text_expire_date', 'file_path', 'other_passport', 'other_passport_detail',
        'other_passport_type', 'other_passport_no', 'other_passport_country_id', 'other_passport_expire_date',
        'scanned_data'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 비자 발급 시 사용한 여권정보를 리턴한다.
     * @param VisaApplication $visa
     * @return VisaPassport|null
     */
    public static function findByVisa(VisaApplication $visa) : ?VisaPassport {
        return static::where('visa_application_id', $visa->id)->get()->first();
    }

    /**
     * 회원 사진을 저장한다.
     * @param UploadedFile $photo
     * @return string
     */
    public static function saveFile(UploadedFile $photo) : string {
        return $photo->store('visa_passports', 'local');
    }

    /**
     * 지정 파일을 삭제한다.
     * @param string $file_path
     * @return void
     */
    public static function deleteFile(?string $file_path = null) : void {
        if(!$file_path) return;
        Storage::disk('local')->delete($file_path);
    }

    /**
     * 비자발급시 사용된 여권정보를 배열로 리턴한다.
     * @param string|null $api_version
     * @return array
     * @OA\Schema (
     *     schema="scanned_passport_data",
     *     title="스켄된 여권 데이터",
     *     @OA\Property(
     *         property="country-code",
     *         type="string",
     *         description="국가코드"
     *     ),
     *     @OA\Property(
     *         property="date-of-birth",
     *         type="string",
     *         description="생년월일"
     *     ),
     *     @OA\Property(
     *         property="date-of-issue",
     *         type="string",
     *         description="여권발급일"
     *     ),
     *     @OA\Property(
     *         property="given-name",
     *         type="string",
     *         description="이름"
     *     ),
     *     @OA\Property(
     *         property="issuing-authority",
     *         type="string",
     *         description="발급지/발급기관"
     *     ),
     *     @OA\Property(
     *         property="middle-name",
     *         type="string",
     *         description="중간이름"
     *     ),
     *     @OA\Property(
     *         property="nationality",
     *         type="string",
     *         description="국적"
     *     ),
     *     @OA\Property(
     *         property="passport-number",
     *         type="string",
     *         description="여권번호"
     *     ),
     *     @OA\Property(
     *         property="place-of-birth",
     *         type="string",
     *         description="출생지"
     *     ),
     *     @OA\Property(
     *         property="sex",
     *         type="string",
     *         description="성별"
     *     ),
     *     @OA\Property(
     *         property="surname",
     *         type="string",
     *         description="이름(성)"
     *     ),
     *     @OA\Property(
     *         property="valid-until",
     *         type="string",
     *         description="만요일"
     *     )
     * )
     *
     * @OA\Schema (
     *     schema="visa_passport",
     *     title="여권정보",
     *     @OA\Property (
     *          property="passport_type",
     *          type="integer",
     *          description="여권종류",
     *     ),
     *     @OA\Property (
     *          property="other_type_detail",
     *          type="string",
     *          description="기타의 경우 설명",
     *     ),
     *     @OA\Property (
     *          property="passport_no",
     *          type="string",
     *          description="여권번호",
     *     ),
     *     @OA\Property (
     *          property="passport_country",
     *          type="object",
     *          description="발급 국가",
     *          ref="#/components/schemas/country"
     *     ),
     *     @OA\Property (
     *          property="issue_place",
     *          type="string",
     *          description="발급지",
     *     ),
     *     @OA\Property (
     *          property="issue_date",
     *          type="string",
     *          format="date",
     *          description="발급일자",
     *     ),
     *     @OA\Property (
     *          property="expire_date",
     *          type="string",
     *          format="date",
     *          description="만료일자",
     *     ),
     *     @OA\Property (
     *          property="other_passport",
     *          type="integer",
     *          description="다른 여권 소지 여부 (0:미소지, 1:소지)",
     *     ),
     *     @OA\Property (
     *          property="other_passport_detail",
     *          type="string",
     *          description="다른 여권 상세 내용",
     *     ),
     *     @OA\Property (
     *          property="other_passport_type",
     *          type="string",
     *          description="다른 여권 종류",
     *     ),
     *     @OA\Property (
     *          property="other_passport_no",
     *          type="string",
     *          description="다른 여권 번호",
     *     ),
     *     @OA\Property (
     *          property="other_passport_country",
     *          type="object",
     *          description="다른 여권 발급 국가",
     *          ref="#/components/schemas/country"
     *     ),
     *     @OA\Property (
     *          property="other_passport_expire_date",
     *          type="string",
     *          format="date",
     *          description="다른 여권 만료 일자",
     *     ),
     *     @OA\Property (
     *          property="scanned_data",
     *          ref="#/components/schemas/scanned_passport_data",
     *          description="스켄된 여권정보",
     *     )
     * )
     */
    public function toInfoArray(?string $api_version = 'v1') : array {
        $data = $this->toArray();
        unset($data['visa_application_id']);
        unset($data['user_id']);
        unset($data['file_path']);
        unset($data['passport_country_id']);
        unset($data['other_passport_country_id']);
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        $data['passport_country'] = Country::findMe($this->passport_country_id)?->toArray();
        $data['other_passport_country'] = Country::findMe($this->other_passport_country_id)?->toArray();
        return $data;
    }

    /**
     * 데이터 삭제시 관련 저장 파일도 삭제한다.
     * @return bool|null
     */
    public function delete() {
        if($this->file_path) Storage::disk('local')->delete($this->file_path);
        return parent::delete(); // TODO: Change the autogenerated stub
    }
}
