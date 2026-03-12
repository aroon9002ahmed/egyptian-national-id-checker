<?php

namespace Aroon\EgyptianNationalId\Rules;

use Aroon\EgyptianNationalId\EgyptianNationalId;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule; // for Laravel 10/11
use Closure;

class NationalIdRule implements Rule, ValidationRule
{
    /**
     * Determine if the validation rule passes. (Laravel <= 9 or Rule contract)
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Only accept strings or integers
        if (!is_string($value) && !is_int($value)) {
            return false;
        }

        $idStr = (string) $value;
        $nationalId = new EgyptianNationalId($idStr);
        return $nationalId->isValid();
    }

    /**
     * Get the validation error message. (Laravel <= 9 or Rule contract)
     *
     * @return string|array
     */
    public function message()
    {
        return 'The :attribute is not a valid Egyptian National ID.';
    }

    /**
     * Run the validation rule. (Laravel 10+ ValidationRule contract)
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail($this->message());
        }
    }
}
