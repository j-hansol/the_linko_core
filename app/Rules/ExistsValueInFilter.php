<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ExistsValueInFilter implements ValidationRule
{
    private bool $nullable = false;

    function __construct(private  string $table, private  string $column, private ?array $filters = null) {}
    public function nullable(bool $nullable = true) : ExistsValueInFilter {$this->nullable = $nullable; return $this;}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        if($this->nullable && is_null($value)) return;

        $values = explode(',', $value);
        $cnt = DB::table($this->table)
            ->when($this->filters, function(Builder $query) {
                foreach($this->filters as $filter) {
                    $query->where($filter['field'], $filter['operator'], $filter['value']);
                }
            })
            ->whereIn($this->column, $values)
            ->count();
        if(count($values) != $cnt) $fail('Some value not exists.');
    }
}
