<?php

namespace App\Rules;

use App\Lib\MemberType;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AvailableUserTypes implements ValidationRule {
    function __construct(private string $type, private User $user) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        $values = explode(',', $value);
        $types = [];
        foreach($values as $value) {
            if( $value instanceof  $this->type) {$types[] = $value; continue;}
            elseif(!is_null($t = $this->type::tryFrom($value))) {$types[] = $t;continue;}
            else {
                $fail('Invalid value.');
                return;
            }
        }

        if($this->user->is_organization == 1) {
            foreach($types as $type) {
                if($type->checkOrganization()) continue;
                else {
                    $fail('This type not allowed.');
                    return;
                }
            }
        }
        else {
            foreach($types as $type) {
                if($type->checkPerson()) continue;
                else {
                    $fail('This type not allowed.');
                    return;
                }
            }
        }
    }
}
