<?php

namespace App\DTOs\V1;

use App\Models\Country;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Traits\Common\DataConvert;
use Illuminate\Http\Request;

class WorkerPassportJsonDto implements IWorkerPassportDto {
    use DataConvert;

    private array $worker_passport_info = [
        'passport_no' => null,
        'passport_country_id' => null,
        'country_iso3_code' => null,
        'country_name' => null,
        'family_name' => null,
        'middle_name' => null,
        'given_names' => null,
        'birthday' => null,
        'birth_place' => null,
        'sex' => null,
        'issue_place' => null,
        'issue_date' => null,
        'expire_date' => null,
        'nationality' => null,
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
        'valid-until' => null,
        'passport_no' => null,
        'passport_country_id' => null,
        'passport_country' => null,
        'issue_place' => null,
        'issue_date' => null,
        'expire_date' => null,
        'given_names' => null,
        'family_name' => null,
        'birthday' => null
    ];

    private array $data_map = [
        'country-code' => null,
        'date-of-birth' => 'birthday',
        'date-of-issue' => 'issue_date',
        'given-name' => 'given_names',
        'issuing-authority' => 'issue_place',
        'middle-name' => 'middle_name',
        'nationality' => 'nationality',
        'passport-number' => 'passport_no',
        'place-of-birth' => 'birth_place',
        'sex' => 'sex',
        'surname' => 'family_name',
        'valid-until' => 'expire_date',
        'passport_no' => 'passport_no',
        'passport_country_id' => 'passport_country_id',
        'passport_country' => 'country_name',
        'issue_place' => 'issue_place',
        'issue_date' => 'issue_date',
        'expire_date' => 'expire_date',
        'given_names' => 'given_names',
        'family_name' => 'family_name',
        'birthday' => 'birthday'
    ];

    // 생성저
    function __construct(array $infos) {
        foreach($infos as $label => $value) {
            if(!array_key_exists($label, $this->data_map) || !$value) continue;

            switch($label) {
                case 'country-code':
                case 'nationality':
                case 'passport_country':
                    $country = Country::findByString($value);
                    if($country) {
                        $this->worker_passport_info['passport_country_id'] = $country->id;
                        $this->worker_passport_info['country_iso3_code'] = $country->iso3_code;
                        $this->worker_passport_info['country_name'] = $country->en_name;
                        $this->worker_passport_info['nationality'] = $value;
                    }
                    break;
                case 'date-of-birth':
                case 'issue_date':
                case 'date-of-issue':
                case 'valid-until':
                case 'expire_date':
                case 'birthday':
                    $date = $this->getDateFromString($value);
                    if($date) $this->worker_passport_info[$this->data_map[$label]] = $date->format('Y-m-d');
                    break;
                default:
                    $this->worker_passport_info[$this->data_map[$label]] = $value;
            }
        }
        $this->worker_passport_info['scanned_data'] = json_encode($this->scanned_info);
    }

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return static
     * @throws HttpErrorsException
     */
    public static function createFromRequest(Request $request) : static {
        $data = json_decode($request->getContent());
        if(!$data)
            throw HttpErrorsException::getInstance([__('errors.json.parse_error')], 400);
        return new static((array)$data);
    }

    // for Model
    public function toArray() : array {
        return $this->worker_passport_info;
    }

    /**
     * 여권번호를 리턴한다.
     * @return string|null
     */
    public function getPassportNo() : ?string {return $this->worker_passport_info['passport_no'];}
}
