<?php

namespace App\Console\Commands;

use App\Lib\LoginMethod;
use App\Lib\MemberType;
use App\Models\Country;
use App\Models\Device;
use App\Models\PasswordHistory;
use App\Models\User;
use App\Models\UserType;
use Database\Factories\UserFactory;
use Faker\Generator;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AddUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add {--T|type= : 회원 유형 번호} {--I|id_alias= : 단체계정 일련번호 별칭} {--A|auto-fill : 내용 자동 채움}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '게정을 추가한다.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $manager = null;
        $type = null;

        if($this->hasOption('id_alias') && $this->option('id_alias')) $manager = User::findByIdAlias($this->option('id_alias'));
        if($this->hasOption('type') && $this->option('type')) $type = MemberType::tryFrom($this->option('type'));
        if(!$type) {
            $types = MemberType::cases();
            $names = [];
            foreach($types as $t) $names[] = $t->value . ':' . $t->name;
            $names[] = '0:Cancel';
            $typename = $this->choice('회원 유형을 선택하세요.', $names);
            $value = explode(':', $typename)[0];
            if($value == '0') {
                $this->info('계정 생성을 중지합니다.');
                return;
            }
            $type = MemberType::tryFrom($value);
            if(!$type) {
                $this->error('회원 유형을 가져오는 중 문제가 발생했습니다.');
                return;
            }
        }

        if(!$manager) {
            $is_continue = true;
            while($is_continue) {
                $id_alias = $this->ask('소속 관리기관의 ID 별칭을 입력하세요. 소속이 없는 경우 공백으로 입력하세요.');
                if($id_alias) {
                    $manager = User::findByIdAlias($id_alias);
                    if($manager) $is_continue = false;
                }
                else $is_continue = false;
            }
        }

        if($manager && !$manager->isOwnType(MemberType::TYPE_MANAGER) &&
            !$manager->isOwnType(MemberType::TYPE_FOREIGN_MANAGER)) {
            $this->error('계정이 소속한 단체는 국내와 해외의 관리기관만 가능합니다.');
            return;
        }

        if($this->hasOption('auto-fill') && $this->option('auto-fill')) {
            $faker = Container::getInstance()->make(Generator::class);
            $user = User::factory()->setData(['type' => $type, 'organization' => $manager])->make();

            $password = $faker->password();
            $hashed_password = Hash::make($password);
            $user->password = $hashed_password;
            $user->save();

            if($user->management_org_id) {
                $manager = User::findMe($user->management_org_id);
                $user->createInitialPassword($manager, $password);
            }
            PasswordHistory::createByUser($user, $hashed_password);

            UserType::createType($user, $type);
            if($type == MemberType::TYPE_GOVERNMENT)
                UserType::createType($user, MemberType::TYPE_ORDER);
            elseif($type == MemberType::TYPE_FOREIGN_GOVERNMENT)
                UserType::createType($user, MemberType::TYPE_RECIPIENT);
            elseif($type == MemberType::TYPE_FOREIGN_PROVIDER) {
                UserType::createType($user, MemberType::TYPE_RECIPIENT);
                UserType::createType($user, MemberType::TYPE_FOREIGN_MANAGER);
                UserType::createType($user, MemberType::TYPE_FOREIGN_MANAGER_OPERATOR);
            }
            Device::createFixedDevice($user);
        }
        else $user = $this->addCustom($type, $manager);

        if($user) {
            $this->table(
                ['ID', 'ID Alias', 'Name', 'Password', 'API Token'],
                [[$user->id, $user->id_alias, $user->name, $user->getInitialPassword(), $user->api_token]]
            );
        }
        else $this->error('계정을 생성할 수 없습니다.');
    }

    /**
     * 내용을 직접 입력하여 계정을 생성한다.
     * @param MemberType $type
     * @param User|null $organization
     * @return User|null
     */
    private function addCustom(MemberType $type, ?User $organization) : ?User {
        $country = null;
        $sex = null;

        $temp_fill = [
            'api_token' => User::genApiToken(),
            'login_method' => LoginMethod::LOGIN_METHOD_PASSWORD,
            'timezone' => 'UTC',
            'active' => 1
        ] + User::genInitialTemporaryIdAlias($type->value);

        if($type->checkKoreaPerson() || $type->checkKoreaOrganization()) $country = Country::findByCode('KR');
        else {
            while (!$country) {
                $t = $this->_askRequired('국적을 입력하세요. 도매인 주소의 국가코드릉 입력합니다.');
                if(!$t) return null;
                $country = Country::findByCode(Str::upper($t));
            }
        }

        if(!($email = $this->_askRequiredUniqueEmail('개인 또는 단체의 전자우편 주소를 입력하세요.'))) return null;
        if(!($cell_phone = $this->_askRequired('개인 또는 단체의 실무자의 휴대전화번호를 입력하세요.'))) return null;
        if(!($address = $this->_askRequired('개인 또는 단체의 주소를 입력하세요.'))) return null;
        $temp_fill += [
            'country_id' => $country->id,
            'email' => $email,
            'cell_phone' => $cell_phone,
            'address' => $address
        ];

        if($type->checkKoreaPerson() || $type->checkForeignPerson()) {
            $temp_fill['is_organization'] = 0;
            if(!($id_alias = $this->_askIdAlias('ID 별칭을 입력하세요.'))) return null;
            if(!($family_name = $this->_askRequired('이름(성)을 입력하세요.'))) return null;
            if(!($given_names = $this->_askRequired('이름을 입력하세요.'))) return null;
            if(!($identity_no = $this->_askRequired('신분증 번호를 입력하세요.'))) return null;
            if(!($birthday = $this->_askRequiredDate('생년월일을 입력하세요.'))) return null;
            $name = User::getPersonName($country, $family_name, $given_names);

            if(($sex = $this->choice('성별을 입력하세요.', ['M', 'F', 'Cancel'])) == 'Cancel') return null;

            $temp_fill['id_alias'] = $id_alias;
            $temp_fill += [
                'family_name' => $family_name,
                'given_names' => $given_names,
                'birthday' => $birthday->format('Y-m-d'),
                'identity_no' => $identity_no,
                'name' => $name,
                'sex' => $sex
            ];
        }
        else {
            $temp_fill['is_organization'] = 1;
            if(!($id_alias = $this->_askIdAlias('ID 별칭을 입력하세요.'))) return null;
            if(!($name = $this->_askRequired('단체명을 입력하세요.'))) return null;
            if(!($registration_no = $this->_askRequired('단체의 사업자(등록)번호를 입력하세요.'))) return null;
            if(!($boss_name = $this->_askRequired('대표자명을 입력하세요.'))) return null;
            if(!($manager_name = $this->_askRequired('담당자명을 입력하세요.'))) return null;
            $telephone = $this->ask('대표 전화번호를 입력하세요.');
            $fax = $this->ask('대표 팩스번호를 입력하세요.');
            $longitude = $this->ask('단체의 위치(경도)를 입력하세요.');
            $latitude = $this->ask('단체의 위치(외도)를 입력하세요.');
            if(!is_numeric($longitude) || !is_numeric($latitude)) {
                $this->error('단체의 위치정보(경도, 위도)가 올바르지 않습니다. 이 내용은 무시됩니다.');
                $longitude = $latitude = null;
            }

            $temp_fill['id_alias'] = $id_alias;
            $temp_fill += [
                'name' => $name,
                'registration_no' => $registration_no,
                'boss_name' => $boss_name,
                'manager_name' => $manager_name,
                'telephone' => $telephone,
                'fax' => $fax,
                'longitude' => $longitude,
                'latitude' => $latitude
            ];
        }

        $confirm = false;
        $password = null;
        while(!$confirm) {
            $password = $this->_askPassword('비밀번호를 입력하세요.');
            $confirm_password = $this->_askPassword('다시 한번 더 비밀번호를 입력하세요.');
            if($password == $confirm_password) $confirm = true;
            else {
                if(Str::upper($this->ask('중단하시겠습니까? (N/y)')) == 'Y') return null;
                else continue;
            }
        }
        if(!$confirm) return null;

        $hashed_password = Hash::make($password);
        $temp_fill['password'] = $hashed_password;
        $temp_fill['management_org_id'] = $organization?->id;
        $user = User::create($temp_fill);

        if($user instanceof User) {
            if($organization) $user->createInitialPassword($organization, $password);
            PasswordHistory::createByUser($user, $hashed_password);
            UserType::createType($user, $type);
            if($type == MemberType::TYPE_GOVERNMENT)
                UserType::createType($user, MemberType::TYPE_ORDER);
            elseif($type == MemberType::TYPE_FOREIGN_GOVERNMENT)
                UserType::createType($user, MemberType::TYPE_RECIPIENT);
            elseif($type == MemberType::TYPE_FOREIGN_PROVIDER) {
                UserType::createType($user, MemberType::TYPE_RECIPIENT);
                UserType::createType($user, MemberType::TYPE_FOREIGN_MANAGER);
                UserType::createType($user, MemberType::TYPE_FOREIGN_MANAGER_OPERATOR);
            }
            Device::createFixedDevice($user);
        }

        return $user;
    }

    /**
     * 사용자로부터 입력을 받아 리턴한다. 공백인 경우 중단 여부를 확인한다.
     * @param string $message
     * @return string|null
     */
    private function _askRequired(string $message) : ?string {
        $is_continue = true;
        $answer = null;

        while ($is_continue) {
            $answer = $this->ask('[필수] ' . $message);
            if(empty(trim($answer))) {
                if(Str::upper($this->ask('중단하시겠습니까? (N/y)')) == 'Y') return null;
                else continue;
            }
            $is_continue = false;
        }

        return $answer;
    }

    /**
     * 필수 입력 항목인 날짜를 입력받는다. 오류가 발생하거나 입력하지 않을 경우 중단 여부를 확인한다.
     * @param string $message
     * @return Carbon|null
     */
    private function _askRequiredDate(string $message) : ?Carbon {
        $is_continue = true;
        $date = null;

        while($is_continue) {
            $answer = $this->ask('[필수] ' . $message);
            try {
                $date = Carbon::createFromFormat('Y-m-d', $answer);
                $is_continue = false;
            } catch (\Exception $e) {
                $this->error('잘 못된 날짜 형식입니다.');
                $date = null;
            }
            if(!$date) {
                if(Str::upper($this->ask('중단하시겠습니까? (N/y)')) == 'Y') return null;
                else continue;
            }
        }

        return $date;
    }

    /**
     * ID 별칭을 입락받아 리턴한다. 입력하지 않거나 중복된 경우 중단 여부를 확인한다.
     * @param string $message
     * @return string|null
     */
    private function _askIdAlias(string $message) : ?string {
        $is_continue = true;
        $id_alias = null;
        while($is_continue) {
            $id_alias = $this->ask($message);
            $user = $id_alias ? User::findByIdAlias($id_alias) : null;
            if($user) {
                $this->error('해당 별칭은 이미 존재합니다.');
                $id_alias = null;
            }
            if(!$id_alias) {
                if(Str::upper($this->ask('중단하시겠습니까? (N/y)')) == 'Y') return null;
                else continue;
            }
            else $is_continue = false;
        }

        return $id_alias;
    }

    /**
     * 전자우편 주소를 입력받는다. 중복되거나 입력하지 않는 경우 중단 여부를 확인한다.
     * @param string $message
     * @return string|null
     */
    private function _askRequiredUniqueEmail(string $message) : ?string {
        $is_continue = true;
        $email = null;

        while($is_continue) {
            $email = $this->ask('[필수] ' . $message);
            $user = $email ? User::findByEmail($email) : null;
            if($user) {
                $this->error('해당 전자우편 주소가 존재합니다.');
                $email = null;
            }
            if(!$email) {
                if(Str::upper($this->ask('중단하시겠습니까? (N/y)')) == 'Y') return null;
                else continue;
            }
            else $is_continue = false;
        }

        return $email;
    }

    /**
     * 비밀번호를 입력받는다. 입력하지 않을 경우 중단 여부를 확인한다.
     * @param string $message
     * @return string|null
     */
    private function _askPassword(string $message) : ?string {
        $is_continue = true;
        $answer = null;

        while ($is_continue) {
            $answer = $this->secret('[필수] ' . $message);
            if(empty(trim($answer))) {
                if(Str::upper($this->ask('중단하시겠습니까? (N/y)')) == 'Y') return null;
                else continue;
            }
            $is_continue = false;
        }

        return $answer;
    }
}
