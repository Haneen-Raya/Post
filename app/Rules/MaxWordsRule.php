<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxWordsRule implements ValidationRule
{

    /**
     * The maximum number of words allowed.
     * @var
     */
    protected $maxWords ;

    /**
     *Create a new rule instance.
     * Sets the maximum number of words allowed.
     * @param int $maxWords
     */
    public function __construct(int $maxWords = 10 ){
        $this->maxWords =  $maxWords ;
    }
    /**
     * Run the validation rule.
     * Counts the words in the value and checks if it exceeds the maximum limit.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!is_string($value)){
            $fail('The attribute : field must be text.');
            return ;
        }
        $wordCount = str_word_count($value);

        if($wordCount > $this->maxWords){
            $fail('The attribute : field must not exceed ' . $this->maxWords .' words. Current count: ' . $wordCount .' words.');
        }
    }
}
