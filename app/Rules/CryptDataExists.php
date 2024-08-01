<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Lib\CryptData as CryptLib;
use Illuminate\Support\Facades\DB;

class CryptDataExists implements ValidationRule
{
    function __construct(public string $table, public string $column) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        if(!($data = CryptLib::decrypt($value, $attribute))) {
            $fail('Invalid Encrypted Data.');
        }
        else {
            $cnt = DB::table($this->table)
                ->where($this->column, $data)
                ->count();
            if($cnt == 0) $fail('Not Exists.');
        }
    }
}
