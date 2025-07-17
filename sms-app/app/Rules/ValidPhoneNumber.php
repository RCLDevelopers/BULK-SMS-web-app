<?php

namespace App\Rules;

use App\Helpers\PhoneNumberHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPhoneNumber implements ValidationRule
{
    /**
     * The default region to use for phone number validation.
     *
     * @var string
     */
    protected $region;

    /**
     * Create a new rule instance.
     *
     * @param string $region The default region to use for validation (e.g., 'US', 'KE')
     * @return void
     */
    public function __construct(string $region = 'KE')
    {
        $this->region = $region;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !PhoneNumberHelper::isValid($value, $this->region)) {
            $fail('The :attribute must be a valid phone number.');
        }
    }
}
