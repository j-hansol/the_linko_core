<?php

namespace App\Models;

use App\Lib\DeviceType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_type', 'user_id', 'name', 'uuid', 'fcm_token', 'ip_address'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 단말기 사용자 정보를 리턴한다.
     * @return User|null
     */
    public function user() : ?User {
        return $this->belongsTo( User::class )->get()->first();
    }

    /**
     * 지정 사용자의 단말기 목록을 리턴한다.
     * @param User $user
     * @return Collection
     */
    public static function getDevicesByUser( User $user, DeviceType $type ) : Collection {
        return static::where( 'user_id', $user->id )
            ->where('device_type', $type->value)->get();
    }

    /**
     * 지정 사용자의 단말기 정보를 리턴한다.
     * @param User $user
     * @param DeviceType $type
     * @return Device|null
     */
    public static function getDeviceByUser(User $user, DeviceType $type) : ?Device {
        return static::getDevicesByUser($user, $type)->first();
    }

    /**
     * 단말기 UUID를 이용하여 단말기정보를 검색하여 가져온다.
     * @param $uuid
     * @return mixed
     */
    public static function findByUUID( $uuid ) : ?Device {
        return static::where('uuid', $uuid )->get()->first();
    }

    /**
     * 지정 단말기 유형의 IP 주소에서 접속한 단말기 정보를 리턴한다.
     * @param User $user
     * @param DeviceType $type
     * @param $ip
     * @return Device|null
     */
    public static function findByIpAddress(User $user, DeviceType $type, $ip) : ?Device {
        return static::where( 'user_id', $user->id )
            ->where('device_type', $type->value)
            ->where('ip_address', $ip)->get()->first();
    }

    /**
     * 현재 접속한 IP를 기반으로 단말기를 검새ㅣㄱ한다.
     * @param User $user
     * @param DeviceType $type
     * @return Device|null
     */
    public static function findByCurrentIpAddress(User $user, DeviceType $type) : ?Device {
        return static::findByIpAddress($user, $type, app('request')->ip());
    }

    /**
     * 오퍼레이터용 고정식 단말기 정보를 생성한다.
     * @param User $user
     * @return Device|null
     */
    public static function createFixedDevice( User $user ) : ?Device {
        try {
            $uuid = null;
            while( !$uuid ) {
                $uuid = (string) Str::uuid();
                $temp = self::findByUUID($uuid);
                if( $temp ) $uuid = null;
            }

            $ip = app('request')->ip();
            $fill = [
                'device_type' => DeviceType::TYPE_FIXED->value, 'user_id' => $user->id,
                'name' => 'Fixed Device', 'uuid' => $uuid,
                'ip_address' => $ip
            ];
            return static::create($fill);
        } catch (\Exception $e) {return null;}
    }

    /**
     * 전달된 정보로 모바일용 단말기 정보를 생성한다.
     * @param User $user
     * @param string $uuid
     * @param string|null $fcm_token
     * @return Device|null
     */
    public static function createMobileDevice(User $user, string $uuid, ?string $fcm_token = null) : ?Device {
        return static::create([
            'user_id' => $user->id,
            'device_type' => DeviceType::TYPE_MOBILE->value,
            'uuid' => $uuid,
            'fcm_token' => $fcm_token
        ]);
    }

    /**
     * 지정 사용자의 고정식 단말기를 리턴한다.
     * @param User $user
     * @return Device|null
     */
    public static function findFixedDevice( User $user ) : ?Device {
        return self::where('user_id', $user->id)
            ->where('device_type', DeviceType::TYPE_FIXED->value)
            ->get()->first();
    }
}
