<?php

namespace App\DTOs\V1;

use App\Lib\CryptDataB64 as CryptData;
use App\Models\Country;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ManagerOperatorDto {
    // 속성
    private ?string $name;
    private ?Country $country;
    private ?string $address;
    private string $hashed_password;

    // 생성자
    function __construct(
        private readonly string $email,
        private readonly string $family_name,
        private readonly string $given_names,
        private readonly string $cell_phone,
        private readonly string $sex,
        private readonly Carbon $birthday,
        private readonly string $password,
    ) {$this->hashed_password = Hash::make($this->password);}

    // Setter, Getter
    public function getEmail() : string {return $this->email;}
    public function getFamilyName() : string {return $this->family_name;}
    public function getGivenNames() : string {return $this->given_names;}

    /**
     * 국가를 바탕으로 회원의 전체 이름을 설정한다.
     * @return void
     * @throws Exception
     */
    public function setName() : void {
        if($this->country) $this->name = User::getPersonName($this->country, $this->family_name, $this->given_names);
        else throw new Exception('country set first');
    }
    public function getName() : string {return $this->name;}
    public function setCountry(Country $country) : void {$this->country = $country;}
    public function getCountry() : ?Country {return $this->country;}
    public function getCellPhone() : string {return $this->cell_phone;}
    public function getSex() : string {return $this->sex;}
    public function getBirthday() : Carbon {return $this->birthday;}
    public function getPassword() : string {return $this->password;}
    public function getHashedPassword() : string {return $this->hashed_password;}
    public function setAddress(?string $address) : void {$this->address = $address;}
    public function getAddress() : ?string {return $this->address;}

    // Creator
    /**
     * 요청 데이터로부터 DTO 체를 생성한다.
     * @param Request $request
     * @return ManagerOperatorDto
     */
    public static function createFromRequest(Request $request) : ManagerOperatorDto {
        $dto = new static(
            CryptData::decrypt($request->input('email'), 'email'),
            $request->input('family_name'),
            $request->input('given_names'),
            CryptData::decrypt($request->input('cell_phone'), 'cell_phone'),
            $request->input('sex'),
            $request->date('birthday', 'Y-m-d'),
            CryptData::decrypt($request->input('password'), 'password')
        );
        $dto->setAddress(CryptData::decrypt($request->input('address'), 'address'));
        return $dto;
    }

    // For model
    public function toArray() : array {
        return [
            'email' => $this->email,
            'country_id' => $this->country->id,
            'cell_phone' => $this->cell_phone,
            'address' => $this->address,
            'family_name' => $this->family_name,
            'given_names' => $this->given_names,
            'name' => $this->name,
            'password' => $this->hashed_password,
            'birthday' => $this->birthday->format('Y-m-d')
        ];
    }
}
