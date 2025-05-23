<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SlugFormatRule implements ValidationRule
{
    /**
     * Run the validation rule.
     * Uses regex to check if the value matches the allowed slug pattern.
     * @param string $attribute
     * @param mixed $value
     * @param \Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!preg_match('/^[a-z0-9-]+$/', $value)){
            $fail('The :attribute syntax is invalid. It should only contain lowercase letters, numbers, and hyphens.');
        }
    }
}
