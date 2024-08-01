<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;

class IdsDto {
    private ?array $ids = null;

    // 생성자
    function __construct(?string $ids) {
        if($ids) $this->ids = explode(',', $ids);
    }

    // Getter
    public function getIds() : ?array {return $this->ids;}

    // Creator
    public static function createFromRequest(Request $request) : IdsDto {
        return new static($request->input('ids'));
    }
}
