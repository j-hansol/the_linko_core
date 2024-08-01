<?php

namespace App\DTOs\Common;

use Illuminate\Http\Request;

class IdDto {
    // 속성
    private array $ids = [];

    // 생성자
    function __construct(Request $request, string $input_field) {
        $temp_ids = explode(',', $request->input($input_field));
        foreach ($temp_ids as $temp_id) {
            $id = trim($temp_id);
            if(is_numeric($id)) $this->ids[] = (int)$id;
        }
    }

    // Getter
    public function getIds() : array {return $this->ids;}

    // Creator
    public static function createFromRequest(Request $request, string $input_field) : static {
        return new static($request, $input_field);
    }
}
