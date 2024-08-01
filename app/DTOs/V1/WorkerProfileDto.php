<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class WorkerProfileDto {
    // 속성
    private ?string $hanja_name;
    private array $another_nationality_ids = [];

    // Setter, Getter
    public function getFamilyName() : string {return $this->family_name;}
    public function getGivenNames() : string {return $this->given_names;}
    public function setHanjaName(?string $name) : void {$this->hanja_name = $name;}
    public function getHanjaName() : ?string {return $this->hanja_name;}
    public function getIdentityNo() :?string {return $this->identity_no;}
    public function getSex() : string {return $this->sex;}
    public function getBirthday() : Carbon {return $this->birthday;}
    public function getCountryId() : int {return $this->country_id;}
    public function getBirthCountryId() : int {return $this->birth_country_id;}
    public function setAnotherNationalityIds(array $ids) : void {
        if(empty($ids)) $this->another_nationality_ids = [];
        else {
            $t = [];
            foreach($ids as $id)
                if(!empty($id)) $t[] = $id;
            $this->another_nationality_ids = !empty($t) ? $t : [];
        }
    }
    public function getAnotherNationalityIds() : array {return $this->another_nationality_ids;}

    // Creator

    /**
     * @param string $family_name
     * @param string $given_names
     * @param string $identity_no
     * @param string $sex
     * @param Carbon $birthday
     * @param int $birth_country_id
     * @throws Exception
     */
    function __construct(
        private readonly string $family_name,
        private readonly string $given_names,
        private readonly string $identity_no,
        private readonly string $sex,
        private readonly Carbon $birthday,
        private readonly int $country_id,
        private readonly int $birth_country_id,
    ) {
        if(!in_array($sex, ['M', 'F'])) throw New Exception('invalid sex');
    }

    /**
     * 요청 데이터로부터 DTO 객체를 생성한다.
     * @param Request $request
     * @return WorkerProfileDto
     * @throws Exception
     */
    public static function createFromRequest(Request $request) : WorkerProfileDto {
        $dto = new static(
            $request->input('family_name'),
            $request->input('given_names'),
            CryptData::decrypt($request->input('identity_no'), 'identity_no'),
            $request->input('sex'),
            Carbon::createfromFormat('Y-m-d', $request->input('birthday')),
            $request->input('country_id'),
            $request->input('birth_country_id')
        );
        $dto->setHanjaName($request->input('hanja_name'));
        $dto->setAnotherNationalityIds(explode(',', $request->input('another_nationality_ids')));
        return $dto;
    }

    // for model
    public function toArray() : array {
        return [
            'family_name' => $this->family_name,
            'given_names' => $this->given_names,
            'hanja_name' => $this->hanja_name,
            'identity_no' => $this->identity_no,
            'sex' => $this->sex,
            'birthday' => $this->birthday,
            'nationality_id' => $this->country_id,
            'birth_country_id' => $this->birth_country_id,
            'another_nationality_ids' => json_encode($this->another_nationality_ids)
        ];
    }
}
