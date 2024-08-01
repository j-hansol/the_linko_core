<?php

namespace App\DTOs\V1;

class ExcelImporterDto {
    function __construct(
        private readonly int $total,
        private readonly int $success,
        private readonly int $errors
    ) {}

    /**
     * 가져오기 결과정보를 배열로 리턴한다.
     * @return array
     */
    public function toArray() : array {
        return [
            'total' => $this->total,
            'success' => $this->success,
            'error' => $this->errors
        ];
    }
}
