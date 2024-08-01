<?php

namespace App\Rules;

use App\Lib\MemberType;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OwnType implements ValidationRule {
    function __construct(public MemberType $type) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        $user = User::findMe($value);
        if(!$user || !$user?->isOwnType($this->type)) $fail('invalid member type');
    }
}
