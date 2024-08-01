<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CertificationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'target_function', 'token', 'expired_at'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 인증 토큰을 생성한다.
     * @param User $user
     * @param string $target_function
     * @param int $expired_seconds
     * @return CertificationToken
     */
    public static function createToken(User $user, string $target_function, ?int $expired_seconds = null) : CertificationToken {
        $token = gen_random_num(8);
        if(!$expired_seconds) $expired_seconds = env('CERTIFICATION_TOKEN_LIFETIME', 300);
        $expired_at = Carbon::now('Asia/Seoul')->addSeconds($expired_seconds);

        return static::create([
            'user_id' => $user->id,
            'target_function' => $target_function,
            'token' => $token,
            'expired_at' => $expired_at->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * 지정 사용자의 토큰을 리턴한다. 기간이 만료된 토큰은 삭제한다.
     * @param User $user
     * @param string $target_function
     * @param string $token
     * @return CertificationToken|null
     */
    public static function getToken(User $user, string $target_function, string $token) : ?CertificationToken {
        static::clearExpiredToken();
        return static::where('user_id', $user->id)
            ->where('target_function', $target_function)
            ->where('token', $token)->get()->first();
    }

    /**
     * 만료된 토큰을 삭제한다.
     * @return void
     */
    public static function clearExpiredToken() : void {
        $now = Carbon::now('Asia/Seoul');
        static::where('expired_at', '<', $now->format('Y-m-d H:i:s'))->delete();
    }
}
