<?php

namespace App\Rules;

use App\Lib\ExcludeItem;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InEnumValues implements ValidationRule {
    function __construct(private readonly string $type) {}
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        $t = explode(',', $value);
        if(empty($t) || !enum_exists($this->type) || !method_exists($this->type, 'tryFrom')) $fail('Invalid Enum Type.');
        else {
            foreach($t as $v) if(!$this->type::tryFrom(trim($v))) $fail('Invalid Enum Values.');
        }
    }
}
