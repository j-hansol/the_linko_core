<?php

namespace App\DTOs\V1;

use App\Lib\PassportType;
use App\Models\Country;
use App\Models\User;
use App\Models\WorkerFamily;
use App\Models\WorkerVisit;
use App\Services\Common\HttpErrorsException;
use App\Services\Common\HttpException;
use App\Traits\Common\DataConvert;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class VisaApplicationJsonDto {
    use DataConvert;

    private User $worker;

    private array $info = [
        'order_stay_period' => null,
        'order_stay_status' => 'Working',
        'profile' => [
            'family_name' => null,
            'given_names' => null,
            'hanja_name' => null,
            'identity_no' => null,
            'sex' => 'M',
            'birthday' => null,
            'text_birthday' => null,
            'nationality_id' => null,
            'nationality' => null,
            'birth_country_id' => null,
            'another_nationality_ids' => null,
            'another_nationality' => null,
            'old_family_name' => null,
            'old_given_names' => null
        ],
        'passport' => [
            'passport_type' => null,
            'other_type_detail' => null,
            'passport_no' => null,
            'passport_country_id' => null,
            'issue_place' => null,
            'issue_date' => null,
            'text_issue_date' => null,
            'expire_date' => null,
            'text_expire_date' => null,
            'other_passport' => 0,
            'other_passport_detail' => null,
            'other_passport_type' => null,
            'other_passport_no' => null,
            'other_passport_country_id' => null,
            'other_passport_expire_date' => null,
            'text_other_passport_expire_date' => null
        ],
        'contact' => [
            'home_address' => null,
            'current_address' => null,
            'cell_phone' => null,
            'email' => null,
            'emergency_full_name' => null,
            'emergency_country_id' => null,
            'emergency_telephone' => null,
            'emergency_relationship' => null
        ],
        'families' => [
            'marital_status' => null,
            'spouse_family_name' => null,
            'spouse_given_name' => null,
            'spouse_birthday' => null,
            'text_spouse_birthday' => null,
            'spouse_nationality_id' => null,
            'spouse_nationality' => null,
            'spouse_residential_address' => null,
            'spouse_contact_no' => null,
            'number_of_children' => 0
        ],
        'education' => [
            'highest_degree' => null,
            'other_detail' => null,
            'school_name' => null,
            'school_location' => null,
        ],
        'employment' => [
            'job' => null,
            'other_detail' => null,
            'org_name' => null,
            'position_course' => null,
            'org_address' => null,
            'org_telephone' => null
        ],
        'visit_detail' => [
            'purpose' => null,
            'other_purpose_detail' => null,
            'intended_stay_period' => null,
            'text_intended_stay_period' => null,
            'intended_entry_date' => null,
            'text_intended_entry_date' => null,
            'address_in_korea' => null,
            'contact_in_korea' => null,
            'visit_korea_ids' => null,
            'visit_country_ids' => null,
            'stay_family_ids' => null,
            'family_member_ids' => null
        ],
        'invitor' => [
            'invitor' => null,
            'invitor_relationship' => null,
            'invitor_birthday' => null,
            'text_invitor_birthday' => null,
            'invitor_registration_no' => null,
            'invitor_address' => null,
            'invitor_telephone' => null,
            'invitor_cell_phone' => null
        ],
        'cost' => [
            'travel_costs' => 0,
            'payer_name' => null,
            'payer_relationship' => null,
            'support_type' => null,
            'payer_contact' => null
        ],
        'assistant' => [
            'assistant_name' => null,
            'assistant_birthday' => null,
            'text_assistant_birthday' => null,
            'assistant_telephone' => null,
            'assistant_relationship' => null
        ]
    ];

    private array $data_map = [
        'family_name' => 'profile.family_name',
        'given_names' => 'profile.given_names',
        'hanja_name' => 'profile.hanja_name',
        'sex: string' => 'profile.sex',
        'birthday' => 'profile.birthday',
        'nationality' => ['profile.nationality_id', 'profile.nationality'],
        'birth_country' => 'profile.birth_country_id',
        'identity_no' => 'profile.identity_no',
        'has_other_names' => '',
        'other_family_name' => 'profile.old_family_name',
        'other_given_name' => 'profile.old_given_names',
        'has_other_citizen_countries' => '',
        'other_citizen_countries' => ['profile.another_nationality_ids', 'profile.another_nationality'],
        'stay_period' => 'order_stay_period',
        'stay_status' => 'order_stay_status',
        'passport' => [
            'passport_type' => 'passport.passport_type',
            'other_type_detail' => 'passport.other_type_detail',
            'passport_no' => 'passport.passport_no',
            'passport_country' => 'passport.passport_country_id',
            'issue_place' => 'passport.issue_place',
            'issue_date' => 'passport.issue_date',
            'expire_date' => 'passport.expire_date',
            'other_passport' => [
                'passport_type' => 'passport.other_passport_type',
                'other_type_detail' => 'passport.other_passport_detail',
                'passport_no' => 'passport.other_passport_no',
                'passport_country' => 'passport.other_passport_country_id',
                'expire_date' => 'passport.other_passport_expire_date',
            ],
        ],
        'other_passport' => [
            'passport_type' => 'passport.other_passport_type',
            'other_type_detail' => 'passport.other_passport_detail',
            'passport_no' => 'passport.other_passport_no',
            'passport_country' => 'passport.other_passport_country_id',
            'expire_date' => 'passport.other_passport_expire_date',
        ],
        'contact' => [
            'home_address' => 'contact.home_address',
            'current_address' => 'contact.current_address',
            'cell_phone' => 'contact.cell_phone',
            'email' => 'contact.email',
            'emergency_full_name' => 'contact.emergency_full_name',
            'emergency_country' => 'contact.emergency_country_id',
            'emergency_telephone' => 'contact.emergency_telephone',
            'emergency_relationship' => 'contact.emergency_relationship',
        ],
        'family' => [
            'marital_status' => 'families.marital_status',
            'spouse' => [
                'family_name' => 'families.spouse_family_name',
                'given_names' => 'families.spouse_given_name',
                'birthday' => 'families.spouse_birthday',
                'nationality' => ['families.spouse_nationality_id', 'families.spouse_nationality'],
                'residential_address' => 'families.spouse_residential_address',
                'contact_no' => 'families.spouse_contact_no',
            ],
            'number_of_children' => 'families.number_of_children',
        ],
        'education' => [
            'highest_degree' => 'education.highest_degree',
            'other_detail' => 'education.other_detail',
            'school_name' => 'education.school_name',
            'school_location' => 'education.school_location',
        ],
        'employment' => [
            'job' => 'employment.job',
            'other_detail' => 'employment.other_detail',
            'org_name' => 'employment.org_name',
            'position_course' => 'employment.position_course',
            'org_address' => 'employment.org_address',
            'org_telephone' => 'employment.org_telephone',
        ],
        'visit_detail' => [
            'purpose' => 'visit_detail.purpose',
            'other_purpose_detail' => 'visit_detail.other_purpose_detail',
            'intended_stay_period' => 'visit_detail.intended_stay_period',
            'intended_entry_date' => 'visit_detail.intended_entry_date',
            'address_in_korea' => 'visit_detail.address_in_korea',
            'contact_in_korea' => 'visit_detail.contact_in_korea',
            'visit_list' => 'visit_detail.visit_korea_ids',
            'visit_countries' => 'visit_detail.visit_country_ids',
            'family_members_in_korea' => 'visit_detail.stay_family_ids',
            'family_members_traveling' => 'visit_detail.family_member_ids'
        ],
        'invitor' => [
            'name' => 'invitor.invitor',
            'birthday_or_registration_no' => ['invitor.invitor_birthday', 'invitor.invitor_registration_no'],
            'relationship' => 'invitor.invitor_relationship',
            'address' => 'invitor.invitor_address',
            'phone_no' => ['invitor.invitor_cell_phone', 'invitor.invitor_telephone']
        ],
        'funding_detail' => [
            'travel_costs' => 'cost.travel_costs',
            'name' => 'cost.payer_name',
            'relationship' => 'cost.payer_relationship',
            'support_type' => 'cost.support_type',
            'contact_no' => 'cost.payer_contact',
        ],
        'assistant' => [
            'full_name' => 'assistant.assistant_name',
            'birthday' => 'assistant.assistant_birthday',
            'phone_no' => 'assistant.assistant_telephone',
            'relationship' => 'assistant.assistant_relationship',
        ]
    ];

    private array $check_items = [
        'visit_detail.visit_korea_ids' => false,
        'visit_detail.visit_country_ids' => false,
        'visit_detail.stay_family_ids' => false,
        'visit_detail.family_member_ids'  => false
    ];


    // 생성자

    /**
     * @param array $request_data
     * @param User $worker
     * @throws HttpErrorsException
     */
    function __construct(array $request_data, User $worker) {
        $this->worker = $worker;

        foreach($request_data as $label => $value) {
            switch ($label) {
                case 'birthday':
                    $this->setValue('text_birthday', $value);
                    $date = $this->getDateFromString(trim($value))?->format('Y-m-d');
                    $this->setValue($this->data_map[$label], $date);
                    break;
                case 'nationality':
                    $country = Country::findByString($value);
                    $this->setValue($this->data_map[$label][1], $value);
                    $this->setValue($this->data_map[$label][0], $country?->id);
                    break;
                case 'birth_country':
                    $country = Country::findByString($value);
                    $this->setValue($this->data_map[$label], $country?->id);
                    break;
                case 'other_citizen_countries':
                    $this->_procOtherCitizenCountries($value, $this->data_map[$label]);
                    break;
                case 'passport':
                case 'other_passport':
                    if($value instanceof \stdClass) $this->_procPassport((array)$value, $this->data_map[$label]);
                    else
                        throw HttpErrorsException::getInstance([__('errors.passport.invalid_passport')], 400);
                    break;
                case 'contact':
                    if($value instanceof \stdClass) $this->_procContact((array)$value, $this->data_map[$label]);
                    else
                        throw HttpErrorsException::getInstance([__('errors.user.invalid_contact_info')], 400);
                    break;
                case 'family':
                    if($value instanceof \stdClass) $this->_procFamily((array)$value, $this->data_map[$label]);
                    else
                        throw HttpErrorsException::getInstance([__('errors.visa.invalid_family_info')], 400);
                    break;
                case 'visit_detail':
                    $this->_procVisitDetail((array)$value, $this->data_map[$label]);
                    break;
                case 'assistant':
                    $this->_procAssistant((array)$value, $this->data_map[$label]);
                    break;
                case 'education':
                case 'employment':
                    if($value instanceof \stdClass) $this->_procSimpleData((array)$value, $this->data_map[$label]);
                    else
                        throw HttpErrorsException::getInstance([__('errors.visa.invalid_employment_info')], 400);
                    break;
                case 'invitor':
                    if($value instanceof \stdClass) $this->_procInvitor((array)$value, $this->data_map[$label]);
                    else
                        throw HttpErrorsException::getInstance([__('errors.visa.invalid_invitor_info')], 400);
                    break;
                case 'funding_detail':
                case 'funding':
                    $t_label = 'funding_detail';
                    if($value instanceof \stdClass) $this->_procSimpleData((array)$value, $this->data_map[$t_label]);
                    else
                        throw HttpErrorsException::getInstance([__('errors.visa.invalid_cost_info')], 400);
                    break;
                default:
                    if(array_key_exists($label, $this->data_map))
                        $this->setValue($this->data_map[$label], $value);
            }
        }
    }

    private function _procOtherCitizenCountries(array $countries, array $targets) : void {
        if(empty($countries)) {
            $this->setValue($targets[0], '[]');
            $this->setValue($targets[1], null);
            return;
        }

        $temp = [];
        foreach($countries as $c) {
            $country = Country::findByString($c);
            if($country) $temp[] = $country->id;
        }
        $this->setValue($targets[0], json_encode($temp));
    }

    private function _getBackupField(string $label) : string {
        $t = explode('.', $label);
        if(count($t) <= 1) return 'text_' . $t[0];

        $t[1] = 'text_' . $t[1];
        return implode('.', $t);
    }

    /**
     * 검토가 필요한 항목을 배열로 리턴한다.
     * @return array
     */
    public function getCheckItems() : array {
        return array_keys(array_filter($this->check_items, function($val){return $val;}));
    }

    /**
     * 여권정보를 파싱하여 저장한다.
     * @param array $values
     * @param array $targets
     * @return void
     * @throws HttpErrorsException
     */
    private function _procPassport(array $values, array $targets) : void {
        if(!array_key_exists('passport_type', $targets))
            $this->setValue($targets['passport_type'], PassportType::TYPE_REGULAR->value);

        foreach($values as $label => $value) {
            switch ($label) {
                case 'passport_country':
                    $country = Country::findByString($value);
                    $this->setValue($targets[$label], $country?->id);
                    break;
                case 'expire_date':
                case 'issue_date':
                    if($value) {
                        $this->setValue($this->_getBackupField($targets[$label]), $value);
                        $date = $this->getDateFromString($value)?->format('Y-m-d');
                        $this->setValue($targets[$label], $date);
                    }
                    break;
                case 'other_passport':
                    if($value instanceof \stdClass) $this->_procPassport((array)$value, $targets[$label]);
                    else
                        throw HttpErrorsException::getInstance([__('errors.passport.invalid_other_passport_info')], 400);
                    break;
                default:
                    if($value && array_key_exists($label, $targets))
                        $this->setValue($targets[$label], $value);
            }
        }

        if($this->info['passport']['other_passport_no']) $this->info['passport']['other_passport'] = 1;
    }

    private function _procContact(array $values, array $targets) : void {
        foreach($values as $label => $value) {
            switch ($label) {
                case 'emergency_country':
                    $country = Country::findByString($value);
                    $this->setValue($targets[$label], $country?->id);
                    break;
                default:
                    if(array_key_exists($label, $targets))
                        $this->setValue($targets[$label], $value);
            }
        }
    }

    private function _procFamily(array $values, array $targets) : void {
        foreach($values as $label => $value) {
            switch ($label) {
                case 'spouse':
                    $this->_procSpouse((array)$value, $targets[$label]);
                    break;
                case 'number_of_children':
                    if($value && is_numeric($value)) {
                        if(array_key_exists($label, $targets))
                            $this->setValue($targets[$label], (int)$value);
                    }
                    break;
                default:
                    if(array_key_exists($label, $targets))
                        $this->setValue($targets[$label], $value);
            }
        }
    }

    private function _procSpouse(array $values, array $targets) : void {
        foreach($values as $label => $value) {
            if(!$value) continue;

            switch ($label) {
                case 'birthday':
                    $this->setValue($this->_getBackupField($targets[$label]), $value);
                    $date = $this->getDateFromString($value)?->format('Y-m-d');
                    $this->setValue($targets[$label], $date);
                    break;
                case 'nationality':
                    $country = Country::findByString($value);
                    $this->setValue($targets[$label][0], $country?->id);
                    $this->setValue($targets[$label][1], $value);
                    break;
                default:
                    if(array_key_exists($label, $targets))
                        $this->setValue($targets[$label], $value);
            }
        }
    }

    private function _procSimpleData(array $values, array $targets) : void {
        foreach($values as $label => $value) {
            if(array_key_exists($label, $targets))
                $this->setValue($targets[$label], $value);
        }
    }

    private function _procAssistant(array $values, array $targets) : void {
        foreach($values as $label => $value) {
            switch ($label) {
                case 'birthday':
                    $this->setValue($this->_getBackupField($targets[$label]), $value);
                    $this->setValue($targets[$label], $this->getDateFromString($value)?->format('Y-m-d'));
                    break;
                case 'contact_no':
                case 'phone_no':
                    $t_label = 'phone_no';
                    if(array_key_exists($t_label, $targets))
                        $this->setValue($targets[$t_label], $value);
                    break;
                default:
                    if(array_key_exists($label, $targets))
                        $this->setValue($targets[$label], $value);
            }
        }
    }

    private function _procInvitor(array $values, array $targets) : void {
        foreach($values as $label => $value) {
            switch ($label) {
                case 'birthday_or_registration_no':
                    $t_values = explode('/', $value);
                    if(count($t_values) > 1) {
                        $date = $this->getDateFromString($t_values[0]);
                        if($date) {
                            $this->setValue($targets[$label][0], $date->format('Y-m-d'));
                            $this->setValue($targets[$label][1], $t_values[1]);
                        }
                        else $this->setValue($targets[$label][1], $value);
                    }
                    else {
                        $date = $this->getDateFromString($t_values[0]);
                        if($date) {
                            $this->setValue($this->_getBackupField($targets[$label][0]), $t_values[0]);
                            $this->setValue($targets[$label][0], $date?->format('Y-m-d'));
                        }
                        else $this->setValue($targets[$label][1], $value);
                    }
                    break;
                case 'phone_no':
                case 'telephone_no':
                case 'office_no':
                    $t_label = 'phone_no';
                    $t_values = explode('/', $value);
                    foreach($t_values as $v) {
                        if(empty($v)) continue;

                        if(preg_match('/\+8210.+/', $v) || preg_match('/\+82\-10.+/', $v) ||
                            preg_match('/010.+/', $v))
                            $this->setValue($targets[$t_label][0], $v);
                        else $this->setValue($targets[$t_label][1], $v);
                    }
                    break;
                default:
                    if(array_key_exists($label, $targets))
                        $this->setValue($targets[$label], $value);
            }
        }
        $t = $this->info['invitor'];
    }

    private function _parseDays(?string $data) : ?int {
        if(!$data) return null;

        $a_days = [
            'YEARS' => 365, 'YEAR' => 365, 'MONTH' => 30, 'MONTHS' => 30, 'DAYS' => 1, 'DAY' => 1,
            '년' => 365, '달' => 30, '월' => 30, '개월' => 30, '일' => 1
        ];

        if(preg_match('/(\d+)\s*([^\d\s]*)\s*/', $data, $matches)) {
            if(count($matches) == 2 && is_numeric($matches[1])) return (int)$matches[1];
            elseif(count($matches) == 3 && is_numeric($matches[1]) && array_key_exists(Str::upper($matches[2]), $a_days)) {
                return (int)$matches[1] * $a_days[Str::upper($matches[2])];
            }
            else return null;
        }
        else return null;
    }

    private function _procVisitDetail(array $values, array $targets) : void {
        foreach ($values as $label => $value) {
            switch ($label) {
                case 'intended_stay_period':
                    $days = $this->_parseDays($value);
                    $this->setValue($this->_getBackupField($targets[$label]), $value);
                    $this->setValue($targets[$label], $days);
                    break;
                case 'intended_entry_date':
                    $this->setValue($this->_getBackupField($targets[$label]), $value);
                    $this->setValue($targets[$label], $this->getDateFromString($value)?->format('Y-m-d'));
                    break;
                case 'visit_list':
                case 'past_visit_list':
                    $t_label = 'visit_list';
                    $this->_procVisitList($targets[$t_label], $value);
                    break;
                case 'visit_countries':
                    $this->_procVisitList($targets[$label], $value, false);
                    break;
                case 'family_members_traveling':
                case 'family_members_in_korea':
                    $this->_procFamilyMember($targets[$label], $value);
                    break;
                default:
                    if(array_key_exists($label, $targets))
                        $this->setValue($targets[$label], $value);
            }
        }
    }

    private function _parsePeriod(string $period) : array {
        $p = preg_replace('/(\s~\s)/', '~', $period);
        $p = preg_replace('/(\s-\s)/', '~', $p);
        $p = str_replace(' ', '~', $p);
        $temp = explode('~', $p);

        $ret = ['entry_date' => null, 'departure_date' => null];
        if(count($temp) <= 3) {
            $ret['entry_date'] = $this->getDateFromString(trim($temp[0]));
            $ret['departure_date'] = $this->getDateFromString(trim($temp[1]));
        }
        return $ret;
    }

    private function _procVisitList(string $target, ?array $list = null, bool $is_korea = true) : void {
        if(!$list || !is_array($list)) $this->setValue($target, '[]');
        $check_point_label = $is_korea ? 'visit_detail.visit_korea_ids' : 'visit_detail.visit_country_ids';
        $temp = [];
        $korea = Country::findByCode('KR');

        foreach($list as $info) {
            $country = $is_korea ? $korea : Country::findByString($info->country_name);
            if(!$info->period_of_stay || !$country || ! $info->visit_purpose) {
                $this->check_items[$check_point_label] = true;
                continue;
            }
            $period = $this->_parsePeriod($info->period_of_stay);
            if($period['entry_date'] && $period['departure_date']) {
                $prev_info = WorkerVisit::query()
                    ->where('user_id', $this->worker->id)
                    ->when($is_korea, function(Builder $query) use ($korea) {
                        $query->where('country_id', $korea->id);
                    })
                    ->when(!$is_korea, function(Builder $query) use ($korea) {
                        $query->where('country_id', '<>', $korea->id);
                    })
                    ->where('entry_date', $period['entry_date']->format('Y-m-d'))
                    ->where('departure_date', $period['departure_date']->format('Y-m-d'))
                    ->get()->first();
                if($prev_info) $temp[] = $prev_info->id;
                else {
                    $new_info = WorkerVisit::create([
                        'user_id' => $this->worker->id,
                        'country_id' => $country->id,
                        'visit_purpose' => $info->visit_purpose,
                        'entry_date' => $period['entry_date']->format('Y-m-d'),
                        'departure_date' => $period['departure_date']->format('Y-m-d'),
                        'period_of_stay' => $info->period_of_stay
                    ]);
                    if($new_info) $temp[] = $new_info->id;
                }
            }
            else {
                $this->check_items[$check_point_label] = true;
                $new_info = WorkerVisit::create([
                    'user_id' => $this->worker->id,
                    'country_id' => $country->id,
                    'visit_purpose' => $info->visit_purpose,
                    'period_of_stay' => $info->period_of_stay
                ]);
                if($new_info) $temp[] = $new_info->id;
            }
        }
        $this->setValue($target, json_encode($temp));
    }

    private function _procFamilyMember(string $target, ?array $list = null) : void {
        if(!$list || !is_array($list)) $this->setValue($target, '[]');
        $check_point_label = $target == 'family_members_traveling' ? 'visit_detail.family_member_ids' : 'visit_detail.stay_family_ids';

        $temp = [];
        foreach($list as $info) {
            $country = Country::findByString($info->nationality);
            $birthday = $this->getDateFromString($info->birthday);
            if(!$country || !$info->birthday) {
                $this->check_items[$check_point_label] = true;
                continue;
            }
            if(!empty($info->full_name)) {
                $saved_member = WorkerFamily::query()
                    ->where('user_id', $this->worker->id)
                    ->where('name', trim($info->full_name))
                    ->get()->first();
                if($saved_member) $temp[] = $saved_member->id;
                else {
                    $saved_member = WorkerFamily::create([
                        'user_id' => $this->worker->id,
                        'country_id' => $country->id,
                        'name' => $info->full_name,
                        'nationality' => $info->nationality,
                        'birthday' => $birthday?->format('Y-m-d'),
                        'text_birthday' => $info->birthday,
                        'relationship' => $info->relationship
                    ]);
                    if($saved_member) $temp[] = $saved_member->id;
                }
            }
        }
        $this->setValue($target, json_encode($temp));
    }

    // Getter
    public function getOrderStayPeriod() : int {return $this->info['order_stay_period'];}
    public function getOrderStayStatus() : string {return $this->info['order_stay_status'];}

    // Creator
    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @param User $worker
     * @return VisaApplicationJsonDto
     * @throws HttpException
     * @OA\Schema (
     *     schema="visa_application_data",
     *     title="비자 신청 데이터",
     *     @OA\Property (property="family_name", type="string", description="이름(성)"),
     *     @OA\Property (property="given_names", type="string", description="이름"),
     *     @OA\Property (property="hanja_name", type="string", description="한자 이름"),
     *     @OA\Property (property="sex", type="string", enum={"M", "F"}, description="성별"),
     *     @OA\Property (property="birthday", type="string", format="date", description="생년월일"),
     *     @OA\Property (property="nationality", type="string", description="국적"),
     *     @OA\Property (property="birth_country", type="string", description="출생국가"),
     *     @OA\Property (property="identity_no", type="string", description="신분증번호"),
     *     @OA\Property (property="has_other_names", type="boolean", description="이전 다른 이름 사용 여부"),
     *     @OA\Property (property="other_family_name", type="string", description="이전 이름(성)"),
     *     @OA\Property (property="other_given_name", type="string", description="이전 이름"),
     *     @OA\Property (property="has_other_citizen_countries", type="boolean", description="다른 국적 보유 여부"),
     *     @OA\Property (
     *         property="other_citizen_countries",
     *         type="array",
     *         @OA\Items(type="string"),
     *         description="다른 국적 이름 목록"
     *     ),
     *     @OA\Property (property="stay_period", type="integer", enum={10,20}, description="체류 기간 (10:단기, 20:장기)"),
     *     @OA\Property (property="stay_status", type="string", description="체류자격"),
     *     @OA\Property (
     *         property="passport",
     *         type="object",
     *         description="여권정보",
     *         @OA\Property (property="passport_type", type="integer", enum={10,20,30,990}, description="여권 종류"),
     *         @OA\Property (property="other_type_detail", type="string", description="여권 종류가 기타인 경우 상세 내용"),
     *         @OA\Property (property="passport_no", type="string", description="여권번호"),
     *         @OA\Property (property="passport_country", type="string", description="발급 국가"),
     *         @OA\Property (property="issue_place", type="string", description="발급지"),
     *         @OA\Property (property="issue_date", type="string", format="date", description="발급일자"),
     *         @OA\Property (property="expire_date", type="string", format="date", description="반료일자")
     *     ),
     *     @OA\Property (property="has_other_passport", type="boolean", description="다른 여권 소지 여부"),
     *     @OA\Property (
     *         property="other_passport",
     *         type="object",
     *         description="다른 여권 정보",
     *         @OA\Property (property="passport_type", type="integer", enum={10,20,30,990}, description="여권 종류"),
     *         @OA\Property (property="other_type_detail", type="string", description="여권 종류가 기타인 경우 상세 내용"),
     *         @OA\Property (property="passport_no", type="string", description="여권번호"),
     *         @OA\Property (property="passport_country", type="string", description="발급 국가"),
     *         @OA\Property (property="expire_date", type="string", format="date", description="반료일자")
     *     ),
     *     @OA\Property (
     *         property="contact",
     *         type="object",
     *         description="연락처 정보",
     *         @OA\Property (property="home_address", type="string", description="본국 주소"),
     *         @OA\Property (property="current_address", type="string", description="현 거주지"),
     *         @OA\Property (property="cell_phone", type="string", description="휴대전화 번호"),
     *         @OA\Property (property="email", type="string", description="전자우편 주소"),
     *         @OA\Property (property="emergency_full_name", type="string", description="비상 연락처 이름"),
     *         @OA\Property (property="emergency_country", type="string", description="거주 국가"),
     *         @OA\Property (property="emergency_telephone", type="string", description="전화번호"),
     *         @OA\Property (property="emergency_relationship", type="string", description="관계"),
     *     ),
     *     @OA\Property (
     *         property="family",
     *         type="object",
     *         description="혼인사항 및 가족사항",
     *         @OA\Property (property="marital_status", type="number", enum={10,20,30}, description="현재 혼인 사항"),
     *         @OA\Property (
     *             property="spouse",
     *             type="object",
     *             description="배우자 정보",
     *             @OA\Property (property="family_name", type="string", description="이름(성)"),
     *             @OA\Property (property="given_names", type="string", description="이름"),
     *             @OA\Property (property="birthday", type="string", format="date", description="생년월일"),
     *             @OA\Property (property="nationality", type="string", description="국적"),
     *             @OA\Property (property="residential_address", type="string", description="거주지"),
     *             @OA\Property (property="contact_no", type="string", description="연락처")
     *         ),
     *         @OA\Property (property="has_children", type="boolean", description="자녀 유무"),
     *         @OA\Property (property="number_of_children", type="integer", description="자녀 수")
     *     ),
     *     @OA\Property (
     *         property="education",
     *         type="object",
     *         description="학력정보",
     *         @OA\Property (property="highest_degree", type="integer", enum={10,20,30,990}, description="최종학력"),
     *         @OA\Property (property="other_detail", type="string", description="기타 학력의 상세 내용"),
     *         @OA\Property (property="school_name", type="string", description="학교이름"),
     *         @OA\Property (property="school_location", type="string", description="학교 소재지")
     *     ),
     *     @OA\Property (
     *         property="employment",
     *         type="object",
     *         description="직업정보",
     *         @OA\Property (property="job", type="integer", enum={10,20,30,40, 50, 60, 70, 990}, description="직업"),
     *         @OA\Property (property="other_detail", type="string", description="기타 직업의 상세 내용"),
     *         @OA\Property (property="org_name", type="string", description="회사/기관/학교명"),
     *         @OA\Property (property="position_course", type="string", description="직위/과정"),
     *         @OA\Property (property="org_address", type="string", description="회사/기관/학교 주소"),
     *         @OA\Property (property="org_telephone", type="string", description="전화번호")
     *     ),
     *     @OA\Property (
     *         property="visit_detail",
     *         type="object",
     *         description="방문정보",
     *         @OA\Property (property="purpose", type="integer", enum={10,20,30,40, 50, 60, 70, 80, 90, 100, 990}, description="방문 목적"),
     *         @OA\Property (property="other_purpose_detail", type="string", description="기타 방문 목적의 상세 내용"),
     *         @OA\Property (property="intended_stay_period", type="integer", description="방문 기간(일)"),
     *         @OA\Property (property="intended_entry_date", type="string", format="date", description="입국 예정일"),
     *         @OA\Property (property="address_in_korea", type="string", description="체류 예정지"),
     *         @OA\Property (property="contact_in_korea", type="string", description="한국내 연락처"),
     *         @OA\Property (property="has_visit_korea", type="boolean", description="최근 5년 이내 한국 방문 내역 유무"),
     *         @OA\Property (
     *             property="visit_list",
     *             type="array",
     *             description="최근 5년 이내의 한국 방문 내역",
     *             @OA\Items (
     *                 type="object",
     *                 @OA\Property (property="visit_purpose", type="string", description="방문 목적"),
     *                 @OA\Property (property="period_of_stay", type="string", description="방문 기간, 예) 2024-01-01 ~ 2024-01-24"),
     *             )
     *         ),
     *         @OA\Property (property="has_visit_countries", type="boolean", description="최근 5년 이내 다른 나라 방문 내역 유무"),
     *         @OA\Property (
     *             property="visit_countries",
     *             type="array",
     *             description="최근 5년 이내의 다른 나라 방문 내역",
     *             @OA\Items (
     *                 type="object",
     *                 @OA\Property (property="country_name", type="string", description="방문 국가명"),
     *                 @OA\Property (property="visit_purpose", type="string", description="방문 목적"),
     *                 @OA\Property (property="period_of_stay", type="string", description="방문 기간, 예) 2024-01-01 ~ 2024-01-24"),
     *             )
     *         ),
     *         @OA\Property (property="has_family_members_in_korea", type="boolean", description="국내 체류 가족 유무"),
     *         @OA\Property (
     *             property="family_members_in_korea",
     *             type="array",
     *             description="국내 체류 가족 내역",
     *             @OA\Items (
     *                 type="object",
     *                 @OA\Property (property="full_name", type="string", description="성명"),
     *                 @OA\Property (property="birthday", type="string", format="date", description="생년월일"),
     *                 @OA\Property (property="nationality", type="string", description="국적"),
     *                 @OA\Property (property="relationship", type="string", description="관계"),
     *             )
     *         ),
     *         @OA\Property (property="has_family_members_traveling", type="boolean", description="당반 입국 가족 유무"),
     *         @OA\Property (
     *             property="family_members_traveling",
     *             type="array",
     *             description="동반 입국 가족 내역",
     *             @OA\Items (
     *                 type="object",
     *                 @OA\Property (property="full_name", type="string", description="성명"),
     *                 @OA\Property (property="birthday", type="string", format="date", description="생년월일"),
     *                 @OA\Property (property="nationality", type="string", description="국적"),
     *                 @OA\Property (property="relationship", type="string", description="관계"),
     *             )
     *         )
     *     ),
     *     @OA\Property (property="has_invitor", type="boolean", description="초청인 유무"),
     *     @OA\Property (
     *         property="invitor",
     *         type="object",
     *         description="초청인 정보",
     *         @OA\Property (property="name", type="string", description="초청인/초청회사명"),
     *         @OA\Property (property="birthday_or_registration_no", type="string", description="생년월일/사업자등록번호"),
     *         @OA\Property (property="relationship", type="string", description="관계"),
     *         @OA\Property (property="address", type="string", description="주소"),
     *         @OA\Property (property="phone_no", type="string", description="전화번호")
     *     ),
     *     @OA\Property (
     *         property="funding_detail",
     *         type="object",
     *         description="방문경비 정보",
     *         @OA\Property (property="travel_costs", type="number", format="double", description="방문경비 (미국 달러 기준)"),
     *         @OA\Property (property="name", type="string", description="방문경비 지출자"),
     *         @OA\Property (property="relationship", type="string", description="관계"),
     *         @OA\Property (property="support_type", type="string", description="지원 내용"),
     *         @OA\Property (property="contact_no", type="string", description="연락처")
     *     ),
     *     @OA\Property (property="has_assistant", type="boolean", description="서류작성 도움 유무"),
     *     @OA\Property (
     *         property="assistant",
     *         type="object",
     *         description="서류작성 두엄 정보",
     *         @OA\Property (property="full_name", type="string", description="성명"),
     *         @OA\Property (property="birthday", type="string", format="date", description="생년월일"),
     *         @OA\Property (property="phone_no", type="string", description="전화번호"),
     *         @OA\Property (property="relationship", type="string", description="관계"),
     *     )
     * )
     */
    public static function createFromRequest(Request $request, User $worker) : VisaApplicationJsonDto {
        $data = json_decode($json_data = $request->getContent());
        Log::info('Input Visa Application Info', [$json_data]);
        if(!$data)
            throw HttpErrorsException::getInstance([__('errors.json.parse_error')], 400);
        return new static((array)$data, $worker);
    }

    // for model
    public function toArray(?User $user = null) : array {
        $t = $this->info;
        /*
        $t['profile']['nationality_id'] = $t['profile']['nationality_id'] ?: $user->country_id;
        $t['profile']['another_nationality_ids'] = $t['profile']['another_nationality_ids'] ?: $user->another_nationality_ids;
        $t['passport']['passport_country_id'] = $t['passport']['passport_country_id'] ?: $user->country_id;
        $t['families']['spouse_nationality_id'] = $t['families']['spouse_nationality_id'] ?: $user->country_id;
        */
        return $t;
    }

    /**
     * 근로자 계정 생성을 위한 배열을 리턴한다.
     * @param User $user
     * @return array
     */
    public function toArrayForWorker(User $user) : array {
        $temp = $this->toArray($user);
        $profile = $temp['profile'];
        $profile['country_id'] = $temp['nationality_id'];
        unset($profile['nationality_id']);
        unset($profile['nationality']);
        unset($profile['another_nationality_ids']);
        $profile['email'] = $temp['passport']['email'];
        $profile['address'] = $temp['contact']['home_address'] ?: $temp['contact']['current_address'];
        $profile['cell_phone'] = $temp['contact']['cell_phone'];
        return $profile;
    }
}
