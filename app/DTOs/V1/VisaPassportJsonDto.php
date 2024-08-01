<?php

namespace App\DTOs\V1;

use App\Models\Country;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Traits\Common\DataConvert;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class VisaPassportJsonDto {
    use DataConvert;

    private array $visa_passport_info = [
        'passport_no' => null,
        'passport_country_id' => null,
        'issue_place' => null,
        'issue_date' => null,
        'expire_date' => null,
        'scanned_data' => null
    ];

    private array $scanned_info = [
        'country-code' => null,
        'date-of-birth' => null,
        'date-of-issue' => null,
        'given-name' => null,
        'issuing-authority' => null,
        'middle-name' => null,
        'nationality' => null,
        'passport-number' => null,
        'place-of-birth' => null,
        'sex' => null,
        'surname' => null,
        'valid-until' => null
    ];

    // 생성저
    function __construct(array $infos) {
        foreach($infos as $label => $value) {
            if(array_key_exists($label, $this->scanned_info)) $this->scanned_info[$label] = $value;
        }
        if($this->scanned_info['country-code']) {
            $country = Country::findByISO3Code($this->scanned_info['country-code']);
            if($country) $this->visa_passport_info['passport_country_id'] = $country->id;
        }
        if($this->scanned_info['date-of-issue']) {
            $issue_date = $this->getDateFromString($this->scanned_info['date-of-issue']);
            $this->visa_passport_info['issue_date'] = $issue_date?->format('Y-m-d');
        }
        if($this->scanned_info['valid-until']) {
            $expire_date = $this->getDateFromString($this->scanned_info['valid-until']);
            $this->visa_passport_info['expire_date'] = $expire_date?->format('Y-m-d');
        }
        $this->visa_passport_info['issue_place'] = $this->scanned_info['issuing-authority'] ?: null;
        $this->visa_passport_info['passport_no'] = $this->scanned_info['passport-number'] ?: null;
        $this->visa_passport_info['scanned_data'] = json_encode($this->scanned_info);
    }

    // Creator
    /**
     * 요청 데이터루부터 JSON 데이터를 받아 DTO 객체를 생성한다.
     * @param Request $request
     * @return VisaPassportJsonDto|null
     * @throws HttpErrorsException
     * @OA\Schema (
     *     schema="passport_data",
     *     title="워권 스켄 데이터",
     *     @OA\Property (property="country-code", type="string", description="국가코드"),
     *     @OA\Property (property="date-of-birth", type="string", format="date", description="생년월일"),
     *     @OA\Property (property="date-of-issue", type="string", format="date", description="발급일자"),
     *     @OA\Property (property="given-name", type="string", description="이름"),
     *     @OA\Property (property="issuing-authority", type="string", description="발급지(기관)"),
     *     @OA\Property (property="middle-name", type="string", description="중간 이름"),
     *     @OA\Property (property="nationality", type="string", description="국적"),
     *     @OA\Property (property="passport-number", type="string", description="여권번호"),
     *     @OA\Property (property="place-of-birth", type="string", description="출생지"),
     *     @OA\Property (property="sex", type="string", description="성별"),
     *     @OA\Property (property="surname", type="string", description="이름(성)"),
     *     @OA\Property (property="valid-until", type="string", format="date", description="만료일"),
     * )
     */
    public static function createFromRequest(Request $request) : ?VisaPassportJsonDto {
        $data = json_decode($request->getContent());
        if(!$data)
            throw HttpErrorsException::getInstance([__('errors.json.parse_error')], 400);
        return new static((array)$data);
    }

    /**
     * 비자 신청정보에 반영 가능한 여권정보를 배열로 리턴한다.
     * @return array
     */
    public function toVisaPassportArray() : array {
        return array_filter($this->visa_passport_info, function($var) {return $var != null;});
    }

    /**
     * 스켄된 여권정보를 배열로 리턴한다.
     * @return array
     */
    public function toArray() : array {return $this->scanned_info;}
}
