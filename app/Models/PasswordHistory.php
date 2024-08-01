<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'password'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * 지정 회원의 비밀번호 변경 내역을 저장한다.
     * @param User $user
     * @param $hashed_password
     * @return void
     */
    public static function createByUser(User $user, $hashed_password) : void {
        static::create([
            'user_id' => $user->id,
            'password' => $hashed_password
        ]);
    }
}
