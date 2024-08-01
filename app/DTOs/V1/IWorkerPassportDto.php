<?php

namespace App\DTOs\V1;

use Illuminate\Http\Request;

interface IWorkerPassportDto {
    public static function createFromRequest(Request $request) : IWorkerPassportDto;
    public function toArray() : array;
}
