<?php

namespace App\Rules;

class RequiredOrNull {
    private bool $required_condition = false;
    public static function __callStatic($method, $parameters) {
        return (new static)->$method(...$parameters);
    }

    public function required(bool $required_condition) : RequiredOrNull {
        $this->required_condition = $required_condition;
        return $this;
    }

    public function __toString() : string {
        return $this->required_condition ? 'required' : 'nullable';
    }
}
