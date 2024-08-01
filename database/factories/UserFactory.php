<?php

namespace Database\Factories;

use App\Lib\LoginMethod;
use App\Lib\MemberType;
use App\Models\Country;
use App\Models\PasswordHistory;
use App\Models\User;
use App\Models\UserType;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory {
    private ?array $data = null;

    /**
     * 추가적인 데이터를 설정한다.
     * @param array $data
     * @return void
     */
    public function setData(array $data) : UserFactory {$this->data = $data; return $this;}

    /**
     * Define the model's default state.
     * @return array
     * @throws Exception
     */
    public function definition(): array {
        if(!empty($this->states)) {
            $country = null;

            $temp_fill = [
                'api_token' => User::genApiToken(),
                'email' => $this->faker->email(),
                'cell_phone' => $this->faker->phoneNumber(),
                'address' => $this->faker->address(),
                'login_method' => LoginMethod::LOGIN_METHOD_PASSWORD,
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'active' => 1
            ];

            if($this->data['organization'] && $this->data['organization'] instanceof User)
                $temp_fill['management_org_id'] = $this->data['organization']->id;

            if(!$this->data['type']) throw new Exception('required member type');
            if($this->data['type'] instanceof MemberType) {
                $temp_fill += User::genInitialTemporaryIdAlias($this->data['type']->value);
                if($this->data['type']->checkKoreaPerson() || $this->data['type']->checkKoreaOrganization())
                    $country = Country::findByCode(env('SERVICE_COUNTRY_CODE', 'KR'));
                else $country = Country::all()->random();
                $temp_fill['country_id'] = $temp_fill['birth_country_id'] = $country?->id;

                if($this->data['type']->checkKoreaPerson() || $this->data['type']->checkForeignPerson()) {
                    $family_name = $this->faker->firstName();
                    $given_names = $this->faker->lastName();
                    $temp_fill += [
                        'is_organization' => 0,
                        'identity_no' => str_number(13),
                        'family_name' => $family_name,
                        'given_names' => $given_names,
                        'name' => User::getPersonName($country, $family_name, $given_names),
                        'sex' => $this->faker->randomElement(['M', 'F']),
                        'birthday' => now()->subYears(20)
                    ];
                }
                else {
                    $temp_fill += [
                        'is_organization' => 1,
                        'registration_no' => str_number(13),
                        'boss_name' => $this->faker->name(),
                        'name' => $this->faker->company(),
                        'manager_name' => $this->faker->name(),
                        'telephone' => $this->faker->phoneNumber(),
                        'fax' => $this->faker->phoneNumber(),
                    ];
                    if($country->code == 'KR') $temp_fill += [
                        'longitude' => $this->faker->longitude(125, 130),
                        'latitude' => $this->faker->latitude(33, 38)
                    ];
                    else $temp_fill += [
                        'longitude' => $this->faker->longitude(),
                        'latitude' => $this->faker->latitude()
                    ];
                }
            }
            else throw new Exception('required member type.');

            return $temp_fill;
        }
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
