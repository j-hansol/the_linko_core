<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccessToken extends Model
{
    use HasFactory;

    const VALID_TIME_LIMIT  = 86400;

    protected $fillable = [
        'token', 'user_id', 'uuid', 'switched_user_id', 'active'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $primaryKey = 'token';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     *
     * @param string $token
     * @return AccessToken|null
     */
    public static function findMe(string $token) : ?AccessToken {
        return static::find($token);
    }

    /**
     * UUID를 이용하여 엑세스토큰을 검색한다.
     * @param $uuid
     * @return mixed
     */
    public static function findByUUID( $uuid ) {
        if( !$uuid ) return null;
        return self::where('uuid', $uuid )->get()->first();
    }

    /**
     * 사용자 정보와 UUID를 이용하여 엑세스 토큰을 생성한다.
     * @param User $user
     * @param Device $device
     * @param bool $active
     * @return AccessToken|null
     */
    public static function createByUserData( User $user, Device $device, bool $active = true) : ?AccessToken {
        $token = $user->id_alias . '-' . Str::random(60);
        $prev_token = self::findByUUID( $device->uuid );
        if( $prev_token ) $prev_token->delete();
        $access_token = self::create([
            'token' => $token,
            'user_id' => $user->id,
            'uuid' => $device->uuid,
            'active' => $active ? 1 : 0
        ]);
        return $access_token;
    }

    /**
     * 현재 사용자의 지정 ID의 엑세스토큰 정보를 가져온다.
     * @param User $user
     * @param $token
     * @return AccessToken|null
     */
    public static function findByForUser( User $user, $token ) : ?AccessToken {
        return self::where('user_id', $user->id)
            ->where('token', $token)->get()->first();
    }

    /**
     * 지정 사용자의 지정 UUID의 엑세스 토큰을 가져온다.
     * @param User $user
     * @param $uuid
     * @return AccessToken|null
     */
    public static function findByUUIDForUser( User $user, $uuid ) : ?AccessToken {
        if( !$uuid ) return null;
        return self::where('user_id', $user->id)
            ->where('uuid', $uuid )->get()->first();
    }

    /**
     * 지정 사용자의 엑세스 토큰을 모두 검색한다.
     * @param User $user
     * @return Collection
     */
    public static function getAccessTokens( User $user ) : Collection {
        return self::where('user_id', $user->id)->get();
    }

    /**
     * 지정 사용자의 모든 엑세스 토큰을 삭제한다.
     * @param User $user
     * @return void
     */
    public static function deleteAllForUser( User $user ) : void {
        self::where('user_id', $user->id)->delete();
    }

    /**
     * 지정 사용자의 만료된 토큰 모두를 삭제한다.
     * @param User $user
     * @return void
     */
    public static function deleteInvalidAccessTokenForUser( User $user ) : void {
        $now = Carbon::now( config('app.timezone') );
        $now->subSeconds( self::VALID_TIME_LIMIT );
        self::where('user_id', $user->id)
            ->where('updated_at', '<', $now->format('Y-m-d H:i:s'))->delete();
    }

    /**
     * 지정 UUID의 토큰정보를 삭제한다.
     * @param $uuid
     * @return void
     */
    public static function deleteByUUID( $uuid ) : void {
        $token = self::findByUUID($uuid);
        if( $token ) $token->delete();
    }

    /**
     * 현재의 토큰이 유효한지 여부를 검사한다.
     * @return bool
     */
    public function isValid() : bool {
        $time = $this->updated_at->getTimestamp();
        $now = Carbon::now(config('app.timezone'))->getTimestamp();
        return ($now - $time) <= self::VALID_TIME_LIMIT;
    }

    /**
     * 엑세스 시간을 갱신한다.
     * @return void
     */
    public function updateAccessTime() : void {
        $this->touch();
    }

    /**
     * 전환계정을 등록한다.
     * @param User $user
     * @return void
     */
    public function setSwitchUser(User $user) : void {
        $this->switched_user_id = $user->id;
        $this->save();
    }

    /**
     * 전환계정 일련번호를 삭제한다.
     * @return void
     */
    public function resetSwitchedUser() : void {
        if($this->switched_user_id) {
            $this->switched_user_id = null;
            $this->save();
        }
    }
}
