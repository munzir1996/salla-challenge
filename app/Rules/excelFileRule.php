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
        //
        $pattern = '/^.+\.(xls|xlsx|csv)$/i';

        if (preg_match($pattern, $value) !== 1) {
            $fail($this->message());
        }
        // return preg_match($pattern, $value) === 1;
    }

    public function message()
    {
        return 'The :attribute must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.';
    }
}
