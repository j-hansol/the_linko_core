<?php

namespace App\Services\Common;

class HttpException extends \Exception {
    function __construct(int $code = 0) {
        parent::__construct('http exception', $code);
    }

    public static function getInstance(int $code) : ?HttpException {return new static($code);}
}
