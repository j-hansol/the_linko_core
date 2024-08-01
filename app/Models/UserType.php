<?php

namespace App\Models;

use App\Lib\MemberType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 회원 유형을 생성한다.
     * @param User $user
     * @param MemberType $type
     * @return UserType|null
     */
    public static function createType(User $user, MemberType $type) : ?UserType {
        return UserType::create(['user_id' => $user->id, 'type' => $type->value]);
    }

    /**
     * 회원 유형 열거형 자료를 리턴한다.
     * @return MemberType
     */
    public function getMemberType() : MemberType {
        return MemberType::tryFrom($this->type);
    }

    /**
     * 지정 회원 계정의 회원 유형 이름을 배열로 리턴한다.
     * @param int $user_id
     * @return array
     */
    public static function getTypeNames(int $user_id) : array {
        $types = static::where('user_id', $user_id)->orderBy('type')->get()->pluck('type')->toArray();
        $names = [];
        foreach($types as $type) $names[] = __("member_type.{$type}");
        return $names;
    }

    /**
     * 지정 회원 계정의 회원 유형 이름을 리스트 형태로 출력한다.
     * @param int $user_id
     * @return string
     */
    public static function getTypeNamesAsList(int $user_id) : string {
        $names = static::getTypeNames($user_id);
        $html = '';
        if($names) {
            $html .= '<ul>';
            foreach($names as $name) $html .= "<li>{$name}</li>";
            $html .= '</ul>';
        }
        return $html;
    }

    /**
     * 지정 회원 유형의 회원 일련번호 목록을 리턴한다.
     * @param MemberType $type
     * @return Collection
     */
    public static function getUserIdsByType(MemberType $type) : Collection {
        return static::where('type', $type->value)->get();
    }

    /**
     * 회원 유형을 전달된 유형으로 갱신한다.
     * @param User $user
     * @param array $types
     * @return void
     * @throws \Exception
     */
    public static function sync(User $user, array $types) : void {
        if(empty($types)) return;

        DB::beginTransaction();
        try {
            $valid_types = [];
            foreach($types as $type) {
                $temp = MemberType::tryFrom($type);
                if(!$temp) throw new \Exception('Invalid Member type.');
                else $valid_types[] = $temp->value;
            }
            $user_types = $user->getTypes()->pluck('type')->toArray();
            $save_targets = array_intersect($valid_types, $user_types);
            $delete_targets = array_merge(
                array_diff($save_targets, $user_types),
                array_diff($user_types, $save_targets),
            );
            static::where('user_id', $user->id)
                ->whereIn('type', $delete_targets)
                ->delete();

            $add_targets = array_merge(
                array_diff($valid_types, $save_targets),
                array_diff($save_targets, $valid_types)
            );
            foreach($add_targets as $target) {
                static::create([
                    'user_id' => $user->id,
                    'type' => $target
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 지정 사용자의 회원 유형을 제거한다.
     * @param User $user
     * @param MemberType $type
     * @return void
     */
    public static function removeType(User $user, MemberType $type) : void {
        static::where('user_id', $user->id)
            ->where('type', $type->value)
            ->delete();
    }

    /**
     * 지정 사용자게 회원 유형을 추가한다.
     * @param User $user
     * @param MemberType $type
     * @return void
     */
    public static function addType(User $user, MemberType $type) : void {
        $cnt = static::where('user_id', $user->id)
            ->where('type', $type->value)
            ->count();
        if($cnt) return;
        else UserType::create(['user_id' => $user->id, 'type' => $type->value]);
    }
}
