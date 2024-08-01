<?php

namespace App\DTOs\V1;

use App\Models\Country;
use App\Models\User;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Traits\Common\DataConvert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\Laravel\Facades\Telegram;

class WorkerAccountJsonDto {
    use DataConvert;

    private array $info =  [
        'email' => null,
        'name' => null,
        'country_id' => null,
        'cell_phone' => null,
        'address' => null,
        'family_name' => null,
        'given_names' => null,
        'hanja_name' => null,
        'identity_no' => null,
        'sex' => null,
        'birthday' => null,
        'birth_country_id' => null,
        'another_nationality_ids' => null,
        'old_family_name' => null,
        'old_given_names' => null,
    ];

    private array $data_map = [
        'family_name' => 'family_name',
        'given_names' => 'given_names',
        'hanja_name' => 'hanja_name',
        'sex' => 'sex',
        'birthday' => 'birthday',
        'nationality' => 'country_id',
        'birth_country' => 'birth_country_id',
        'identity_no' => 'identity_no',
        'other_family_name' => 'old_family_name',
        'other_given_name' => 'old_given_names',
        'other_citizen_countries' => 'another_nationality_ids',
        'contact' => [
            'home_address' => 'address',
            'cell_phone' => 'contact.cell_phone',
            'email' => 'contact.email',
        ],
    ];

    private bool $is_able = false;

    // 생성자

    /**
     * @param array $request_data
     * @param User $manager
     * @throws HttpErrorsException
     */
    function __construct(array $request_data, User $manager) {
        foreach($request_data as $label => $value) {
            switch ($label) {
                case 'birthday':
                    $this->setValue($this->data_map[$label], $this->getDateFromString(trim($value))?->format('Y-m-d'));
                    break;
                case 'nationality':
                case 'birth_country':
                $country = Country::findByString($value);
                    $this->setValue($this->data_map[$label], $country?->id);
                    break;
                case 'other_citizen_countries':
                    $this->_procOtherCitizenCountries($value, $this->data_map[$label]);
                    break;
                case 'contact':
                    if($value instanceof \stdClass) $this->_procContact((array)$value, $this->data_map[$label]);
                    else throw HttpErrorsException::getInstance([__('errors.user.invalid_contact_info')], 400);
                    break;
                default:
                    if(array_key_exists($label, $this->data_map))
                        $this->setValue($this->data_map[$label], $value);
            }
        }
        if(!$this->info['country_id']) $this->info['country_id'] = $manager->country_id;
        if(!$this->info['birth_country_id']) $this->info['birth_country_id'] = $manager->country_id;
        $country = Country::findMe($this->info['country_id']);
        $this->info['name'] = $country
            ? User::getPersonName($country, $this->info['family_name'], $this->info['given_names'])
            : "{$this->info['given_names']} {$this->info['family_name']}";
    }

    // Getter
    public function getEmail() : ?string {return $this->info['email'];}

    /**
     * 다르 국적 소지 데이터를 처리한다.
     * @param array $countries
     * @param string $target
     * @return void
     */
    private function _procOtherCitizenCountries(array $countries, string $target) : void {
        if(empty($countries)) {
            $this->setValue($target, '[]');
            return;
        }

        $temp = [];
        foreach($countries as $c) {
            $country = Country::findByString($c);
            if($country) $temp[] = $country->id;
        }
        $this->setValue($target, json_encode($temp));
    }

    /**
     * 연ㅇ락처 정보를 설정한다.
     * @param array $values
     * @param array $targets
     * @return void
     */
    private function _procContact(array $values, array $targets) : void {
        foreach($values as $label => $value) {
            if(array_key_exists($label, $targets))
                $this->setValue($targets[$label], $value);
        }
    }

    // for Model
    public function toArray() : array {
        return $this->info;
    }

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @param User $manager
     * @return static
     * @throws HttpErrorsException
     */
    public static function createFromRequest(Request $request, User $manager) : static {
        $data = json_decode($json_date = $request->getContent());
        if(!$data)
            throw HttpErrorsException::getInstance([__('errors.json.parse_error')], 400);
        return new static((array)$data, $manager);
    }

    /**
     * 계정 생성을 위한 유효성 검사 결과를 리턴한다.
     * @return bool
     * @throws HttpErrorsException
     */
    public function isCreateAble() : bool {
        $validator = Validator::make($this->info, [
            'family_name' => ['required'],
            'given_names' => ['required'],
            'hanja_name' => ['nullable'],
            'identity_no' => ['nullable'],
            'sex' => ['required', 'in:M,F'],
            'birthday' => ['nullable', 'date', 'date_format:Y-m-d'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'birth_country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'another_nationality_ids' => ['nullable'],
            'address' => ['nullable'],
            'cell_phone' => ['nullable'],
            'email' => ['required', 'email', 'unique:users,email']
        ]);
        $result = $validator->passes();
        if(!$result) {
            $errors = $validator->getMessageBag()->toArray();
            Log::error('No Creatable Member', $errors);
            telegram_message('아래와 같은 이유로 비자신청정보를 이용한 계정 생성은 할 수 없습니다.', $errors);
            throw HttpErrorsException::getInstance($errors, 406);
        }
        return $result;
    }
}
