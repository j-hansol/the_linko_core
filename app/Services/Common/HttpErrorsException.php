<?php

namespace App\Services\Common;

use Exception;

class HttpErrorsException extends Exception {
    function __construct(private readonly array $errors, int $code = 400) {
        parent::__construct('Bad Request', $code);
    }

    public static function getInstance(array $errors, int $code) : static {return new static($errors, $code);}
    public function getErrors() : ?array {return $this->errors;}
}
