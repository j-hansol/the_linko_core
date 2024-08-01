<?php

namespace App\Traits\Common;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

trait FindByUser {
    private static function _findByUser(User $user) : Collection {
        return static::where('user_id', $user->id)
            ->get();
    }
    public static function findOneByUser(User $user) : static {
        return static::_findByUser($user)->first();
    }

    public static function findAllByUser(User $user) : Collection {
        return static::_findByUser($user);
    }
}
