<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ExistsValues implements ValidationRule {
    private bool $nullable = false;

    function __construct(private  string $table, private  string $column) {}
    public function nullable(bool $nullable = true) : ExistsValues {$this->nullable = $nullable; return $this;}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        if($this->nullable && is_null($value)) return;

        $values = explode(',', $value);
        $cnt = DB::table($this->table)
            ->whereIn($this->column, $values)
            ->count();
        if(count($values) != $cnt) $fail('Some value not exists.');
    }
}

