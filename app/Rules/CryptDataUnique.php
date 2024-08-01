<?php

namespace App\Rules;

use App\Lib\CryptDataB64 as CryptLib;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CryptDataUnique implements ValidationRule {
    private ?int $ignore_id = null;

    function __construct(public string $table, public string $column) {}
    public function ignore(int $id) : CryptDataUnique {$this->ignore_id = $id; return $this;}

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
                ->when($this->ignore_id, function(Builder $query, int $id) {
                    $query->where('id', '<>', $id);
                })
                ->count();
            if($cnt != 0) $fail('Not Unique.');
        }
    }
}
