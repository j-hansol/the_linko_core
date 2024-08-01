<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InArrayValues implements ValidationRule {
    function __construct(private readonly array $values) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        $t = explode(',', $value);
        foreach($t as $v) {
            if(!in_array(trim($v), $this->values))
                $fail('Invalid Values.');
        }
    }
}
