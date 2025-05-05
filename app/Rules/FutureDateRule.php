<?php

namespace App\Rules;

use Closure;
use Exception;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;

class FutureDateRule implements ValidationRule
{
    /**
     * Run the validation rule.
     * Checks if the provided date value is today or in the future.
     * @param string $attribute
     * @param mixed $value
     * @param \Closure $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value) || $value === '') {
            return;
        }
            try{
                $data = Carbon::parse($value);
                if($data->isPast() && !$data->isToday()){
                    $fail('The :attribute must be a date in the future.');
                }
            }
            catch(Exception $e){
                $fail( 'The :attribute format is invalid.');
            }
        }
    }

