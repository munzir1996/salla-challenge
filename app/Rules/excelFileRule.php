<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class excelFileRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern = '/^.+\.(xls|xlsx|csv)$/i';

        if (preg_match($pattern, $value) !== 1) {
            $fail($this->message());
        }
    }

    public function message()
    {
        return 'The :attribute must be excel file.';
    }
}
