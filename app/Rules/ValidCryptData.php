<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Lib\CryptDataB64 as CryptLib;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ValidCryptData implements ValidationRule {
    private ?string $data_type = null;
    private bool $nullable = false;
    private ?string $unique_table_name = null;
    private ?string $unique_column_name = null;
    private bool|Closure $is_required = false;

    public function type(string $type) : ValidCryptData {$this->data_type = $type; return $this;}
    public function nullable(bool $nullable = true) : ValidCryptData {$this->nullable = $nullable; return $this;}
    public function required(bool $is_required) : ValidCryptData {$this->is_required = $is_required; return $this;}
    public function requiredOrNull(bool $flag) : ValidCryptData {
        $this->is_required = $flag;$this->nullable = !$flag;
        return $this;
    }
    public function unique(string $table_name, string $column_name) : ValidCryptData {
        $this->unique_column_name = $column_name;
        $this->unique_table_name = $table_name;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        if($this->nullable && is_null($value) && !$this->is_required) return;

        if(!($data = CryptLib::decrypt($value, $attribute))) $fail('Invalid Encrypted Data.');
        elseif($this->data_type) {
            if($this->data_type == 'email' && !filter_var($data, FILTER_VALIDATE_EMAIL)) $fail('Invalid Email');
        }

        if($this->unique_table_name && $this->unique_column_name) {
            $cnt = DB::table($this->unique_table_name)
                ->where($this->unique_column_name, $data)
                ->count();
            if($cnt != 0) $fail("The ${attribute} has already been taken.");
        }
    }
}
