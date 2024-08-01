<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\JsonResponses\Common\Data;
use App\Lib\CryptDataB64 as CryptData;
use App\Lib\MemberType;
use App\Traits\Common\FindMe;
use Carbon\Carbon;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Annotations as OA;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, CanResetPassword, FindMe;

    private bool $switched_user = false;
    private ?Collection $types = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // 식별자 정보
        'id_alias', 'email', 'api_token',

        // 공통 필드
        'is_organization', 'name', 'country_id', 'timezone', 'photo', 'cell_phone', 'address',

        // 개인 프로필
        'family_name', 'given_names', 'hanja_name', 'identity_no', 'sex', 'birthday',
        'birth_country_id', 'another_nationality_ids', 'old_family_name', 'old_given_names',
        'management_org_id',

        // 단체 프로필
        'registration_no', 'boss_name', 'manager_name', 'telephone', 'fax', 'road_map',
        'longitude', 'latitude',

        // 인증관련 필드
        'login_method', 'password', 'auth_provider', 'auth_provider_identifier', 'active',
        'email_verified_at', 'remember_token',

        // 시스템 이용 정보
        'create_year', 'create_month', 'create_day'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 지정 이메일 주소로 사용자를 검색한다.
     * @param string $email
     * @return User|null
     */
    public static function findByEmail(string $email) : ?User {
        return static::where('email', $email)->get()->first();
    }

    /**
     * 지정 별칭으로 사용자를 검색한다.
     * @param string $id_alias
     * @return User|null
     */
    public static function findByIdAlias(string $id_alias) : ?User {
        return static::where('id_alias', $id_alias)->get()->first();
    }

    /**
     * 지정 회원 유형이 올바른지 여부를 판단한다.
     * @param int $type
     * @return bool
     */
    public static function isValidType(int $type) : bool {
        return MemberType::tryFrom($type) != null;
    }

    /**
     * 지정 회원 유형의 초기 ID 별칭을 리턴한다.
     * @param int $type
     * @return array|null
     */
    public static function genInitialTemporaryIdAlias(int $type) : ?array {
        $prefix = MemberType::getPrefix($type);
        if(!$prefix) return null;

        $today = Carbon::now( config('app.timezone') );
        $date = get_date_string( $today->year, $today->month, $today->day );
        $gen_id = null;
        do {
            $rid = gen_random_num(6);
            $sid = $prefix . $date . $rid;
            $cnt = static::where('create_year', $today->year)
                ->where('create_month', $today->month)
                ->where('create_day', $today->day)
                ->where('id_alias', $sid)
                ->count();
            if($cnt == 0) $gen_id = $sid;
        } while($gen_id == null);
        return [
            'create_year' => $today->year,
            'create_month' => $today->month,
            'create_day' => $today->day,
            'id_alias' => $gen_id
        ];
    }

    /**
     * 본인 여부를 판단한다.
     * @param User $user
     * @return bool
     */
    public function isMe(User $user) : bool {
        return $this->id == $user->id;
    }

    /**
     * 현재 사용자의 지정 회원 유형으로 변경하고 회원코드(id_alias)를 다시 생성한 후 반영한다.
     * @param int $type
     * @return void|null
     */
    public function regenIdAlias(int $type) {
        $prefix = MemberType::getPrefix($type);
        if(!$prefix) return null;

        $date = get_date_string( $this->year, $this->month, $this->day );
        $gen_id = null;
        do {
            $rid = gen_random_num(4);
            $sid = $prefix . $date . $rid;
            $cnt = static::where('type', MemberType::TYPE_NONE->value)
                ->where('create_year', $this->year)
                ->where('create_month', $this->month)
                ->where('create_day', $this->day)
                ->where('id', '<>', $this->id)
                ->where('id_alias', $sid)
                ->count();
            if($cnt == 0) $gen_id = $sid;
        } while($gen_id == null);

        $this->id_alias = $gen_id;
        $this->type = $type;
        $this->save();
    }

    /**
     * 회원 계정에서 유일한 API 토큰 문자열을 리턴한다.
     * @return string
     */
    public static function genApiToken(?User $user = null) : string {
        $is_unique = false;
        $token = null;
        while(!$is_unique) {
            $token = Str::random(60);
            $query = static::query();
            if($user) $query->where('id', '<>', $user->id);

            $is_unique = $query->where('api_token', $token)->count() == 0;
        }

        return $token;
    }

    /**
     * 소속 국가에 따라 개인 이름을 출력한다.
     * @param Country $country
     * @param string $family_name
     * @param string $given_names
     * @return string
     */
    public static function getPersonName(Country $country, string $family_name, string $given_names) : string {
        return __('base.person_name',[
            'family_name' => $family_name,
            'given_names' => $given_names
        ], $country->code == 'KR' ? 'ko' : 'en');
    }

    /**
     * 비밀번호를 변경한다.
     * @param string $password
     * @return void
     * @throws \Exception
     */
    public function updatePassword(string $password) : void {
        DB::beginTransaction();
        try {
            $hashed_password = Hash::make($password);
            $this->password = $hashed_password;
            $this->save();

            PasswordHistory::createByUser($this, $hashed_password);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 전환된 사용자 계정 여부를 설정한다.
     * @param bool $flag
     * @return void
     */
    public function setSwitchedUser(bool $flag = false) : void {
        $this->switched_user = $flag;
    }

    /**
     * 전환된 사용자 계정인지 여부를 판단한다.
     * @return bool
     */
    public function isSwitchedUser() : bool {
        return $this->switched_user;
    }

    /**
     * 회원 유형 정보를 리턴한다.
     * @return Collection
     */
    public function getTypes(bool $flush = false) : Collection {
        if($flush) $this->types = null;
        return $this->types ?: $this->types = $this->hasMany(UserType::class, 'user_id')->get();
    }

    /**
     * 지정 회원 유형을 소유하고 있는지 여부플 판단한다.
     * @param MemberType $type
     * @return bool
     */
    public function isOwnType(MemberType $type) : bool {
        $types = $this->getTypes()->pluck('type')->toArray();
        return in_array($type->value, $types);
    }

    /**
     * 회원 유형 중 국내 단체 회원 유형을 소유가하고 있는지 여부플 판단한다.
     * @return bool
     */
    public function isOwnKoreaOrganizationType() : bool {
        $types = $this->getTypes();
        foreach($types as $type) {
            if($type->getMemberType()->checkKoreaOrganization()) return true;
        }

        return false;
    }

    /**
     * 회원 유형 중 해외 단체 회원 유형을 소유하고 있는지 여부를 판단한다.
     * @return bool
     */
    public function isOwnForeignOrganizationType() : bool {
        $types = $this->getTypes();
        foreach($types as $type) {
            if($type->getMemberType()->checkForeignOrganization()) return true;
        }

        return false;
    }

    /**
     * 국내 해외를 구분하지 않고 단체 회원 유형을 소유하고 있는지 여부를 판단한다.
     * @return bool
     */
    public function isOwnOrganizationType() : bool {
        return $this->isOwnForeignOrganizationType() || $this->isOwnKoreaOrganizationType();
    }

    /**
     * 회원 유형 중 해외 개인 회원 유형을 소유하고 있는지 여부를 판단한다.
     * @return bool
     */
    public function isOwnPersonType() : bool {
        $types = $this->getTypes();
        foreach($types as $type) {
            if($type->getMemberType()->checkPerson()) return true;
        }

        return false;
    }

    /**
     * 회원 유형 중 서비스 운영자 또는 관기기관 실무자 유형을 소유하고 있는지 여부를 판단한다.
     * @return bool
     */
    public function isOwnOperatorType() : bool {
        $types = $this->getTypes();
        foreach($types as $type) {
            if($type->getMemberType()->checkOperator()) return true;
        }

        return false;
    }

    /**
     * 회원 국적의 국가코드를 리턴한다.
     * @return string|null
     */
    public function getCountryCode() : ?string {
        $country = Country::find($this->country_id);
        if($country) return $country->code;
        else return null;
    }

    /**
     * 회원 국적의 국가이름을 리턴한다.
     * @return string|null
     */
    public function getCountryName() : ?string {
        $country = Country::find($this->country_id);
        if($country) return $country->name;
        else return null;
    }

    /**
     * 회원 사진을 저장한다.
     * @param UploadedFile $photo
     * @return string
     */
    public static function savePhoto(UploadedFile $photo) : string {
        return $photo->store('user_photos', 'local');
    }

    /**
     * 회원 사진을 지정한 경로의 파일로 변경한다.
     * @param string $path
     * @return void
     */
    public function updatePhoto(string $path) : void {
        if($this->photo) Storage::disk('local')->delete($this->photo);
        $this->photo = $path;
        $this->save();
    }

    /**
     * 단체 회원의 약도 이미지를 저장한다.
     * @param UploadedFile $road_map
     * @return string
     */
    public static function saveRoadMap(UploadedFile $road_map) : string {
        return $road_map->store('organization_road_maps', 'local');
    }

    /**
     * 단체 회원 약도 이미지를 지정 경로의 파일로 변경한다.
     * @param string $path
     * @return void
     */
    public function updateRoadMap(string $path) : void {
        if($this->road_map) Storage::disk('local')->delete($this->road_map);
        $this->road_map = $path;
        $this->save();
    }

    /**
     * 회원정보를 배열로 리턴한다.
     * @return array
     */
    public function toInfoArray() : array {
        $country = Country::findMe($this->country_id)?->toArray();
        $birth_country = Country::findMe($this->birth_country_id)?->toArray();
        $t_another_nationalities = $this->another_nationality_ids ?
            Country::find(json_decode($this->another_nationality_ids)) : null;
        if($t_another_nationalities) {
            $another_nationalities = [];
            foreach($t_another_nationalities as $c) $another_nationalities[] = $c->toArray();
        } else $another_nationalities = null;

        $access_token = access_token();
        $photo_url = $this->photo ?
            route('api.v1.user.show_photo', ['id' => $this->id, '_token' => $access_token])
            : null;
        $road_map_url = $this->road_map ?
            route('api.v1.user.show_road_map', ['id' => $this->id, '_token' => $access_token])
            : null;

        return [
            'id' => $this->id,
            'user_types' => $this->getTypes()->pluck('type')->toArray(),
            'id_alias' => CryptData::encrypt($this->id_alias),
            'email' => CryptData::encrypt($this->email),
            'is_organization' => $this->is_organization == 1,
            'name' => $this->name,
            'country' => $country,
            'photo' => $photo_url,
            'cell_phone' => CryptData::encrypt($this->cell_phone),
            'address' => CryptData::encrypt($this->address),
            'family_name' => $this->family_name,
            'given_names' => $this->given_names,
            'hanja_name' => $this->hanja_name,
            'identity_no' => $this->identity_no ? CryptData::encrypt($this->identity_no) : null,
            'sex' => $this->sex,
            'birthday' => $this->birthday,
            'birth_country' => $birth_country,
            'another_nationalities' => $another_nationalities,
            'old_family_name' => $this->old_family_name,
            'old_given_names' => $this->old_given_names,
            'registration_no' => $this->registration_no,
            'boss_name' => $this->boss_name,
            'manager_name' => $this->manager_name ? CryptData::encrypt($this->manager_name) : null,
            'telephone' => $this->telephone,
            'fax' => $this->fax,
            'road_map' => $road_map_url,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'organization' => User::findMe($this->management_org_id)?->toSimpleArray()
        ];
    }

    /**
     * 사용자의 최소한의 정보를 배열로 리턴한다.
     * @OA\Schema (
     *     schema="simple_user_info",
     *     title="회원정보",
     *     @OA\Property (
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     ),
     *     @OA\Property (
     *          property="id_alias",
     *          type="string",
     *          description="암호화된 ID 별칭"
     *     ),
     *     @OA\Property (
     *          property="name",
     *          type="string",
     *          description="이름 (개인 또는 단체명)"
     *     )
     * )
     */
    public function toSimpleArray() : array {
        return [
            'id' => $this->id,
            'id_alias' => $this->id_alias,
            'name' => $this->name
        ];
    }

    /**
     * 관리기관에 의해 성성된 계정의 초기 비밀번호를 저장한다.
     * @param User $creator
     * @param string $password
     * @return bool
     */
    public function createInitialPassword(User $creator, string $password) : bool {
        try {
            DB::table('initial_passwords')
                ->insert([
                    'user_id' => $this->id,
                    'creator_id' => $creator->id,
                    'password' => $password
                ]);
            return true;
        } catch (\Exception $e) {return false;}
    }

    /**
     * 지정 근로자가 관리중인 근로자인지 판단한다.
     * @param User $user
     * @return bool
     */
    public function isInManagementUser(User $user) : bool {
        if($this->isOwnType(MemberType::TYPE_FOREIGN_MANAGER)) $manager = $this;
        elseif($this->isOwnType(MemberType::TYPE_FOREIGN_MANAGER_OPERATOR)) $manager = User::findMe($this->management_org_id);
        else return false;

        return $manager?->id == $user->management_org_id;
    }

    /**
     * 관리기관 소속 실무자가 속한 관리기관 계정을 리턴한다.
     * @return User|null
     */
    public function getAffiliationManager() : ?User {
        if($this->isOwnType(MemberType::TYPE_FOREIGN_MANAGER)) return $this;
        elseif($this->isOwnType(MemberType::TYPE_FOREIGN_MANAGER_OPERATOR)) return User::findMe($this->management_org_id);
        else return null;
    }

    /**
     * 초기비밀번호가 저장되어 있는 경우, 암호화된 비밀번호를 리턴한다.
     * @return string|null
     */
    public function getInitialPassword() : ?string {
        try {
            $info = DB::table('initial_passwords')
                ->where('user_id', $this->id)
                ->get()->first();
            if($info) return $info->password;
            else return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 현재 사용자 정보로 응답한다.
     * @return JsonResponse
     */
    public function response() : JsonResponse {
        return new Data($this->toInfoArray());
    }

    /**
     * 현재 계정 일련번호로 응답한다.
     * @return JsonResponse
     * @OA\Schema (
     *     schema="id",
     *     title="일련번호",
     *     @OA\Property (
     *          property="id",
     *          type="integer",
     *          description="일련번호"
     *     )
     * )
     */
    public function responseId() : JsonResponse {
        return new Data(['id' => $this->id]);
    }

    /**
     * 근로자의 학력정보를 리턴한다.
     * @return Collection
     */
    public function getEducation() : Collection {
        return $this->hasMany(WorkerEducation::class, 'user_id')->get();
    }

    /**
     * 근로자 경력정보를 리턴한다.
     * @return Collection
     */
    public function getExperiences() : Collection {
        return $this->hasMany(WorkerExperience::class, 'user_id')->get();
    }

    /**
     * 파일을 삭제한다.
     * @return void
     */
    public function deleteFileResource() : void {
        if($this->photo) Storage::disk('local')->delete($this->photo);
        if($this->road_map) Storage::disk('local')->delete($this->road_map);
    }

    /**
     * 파일 경로 저장 필드 및 저장 경로를 리턴한다.
     * @return array
     */
    public static function basePath() : array {
        return [
            'photo' => 'user_photos',
            'road_map' => 'organization_road_maps'
        ];
    }
}
