<?php

namespace App\Traits\Common;

use App\Models\OrderTask;

trait FindMe {
    /**
     * 데이터를 검색하여 리턴한다.
     * @param int|null $id
     * @return OrderTask|null
     */
    public static function findMe(?int $id) : ?static {
        if(!$id) return null;
        return static::find($id);
    }
}
