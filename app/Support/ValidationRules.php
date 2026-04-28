<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/**
 * Shared validation rules for the API.
 */
final class ValidationRules
{
    /**
     * Email format (RFC) plus DNS check for a domain that can receive mail (MX / A for SMTP).
     * Use for addresses that will receive transactional email (register, password reset, forms).
     *
     * @see \Illuminate\Validation\Rules\Email::validateMxRecord()
     */
    public static function emailDns(): \Illuminate\Validation\Rules\Email
    {
        return Rule::email()->rfcCompliant()->validateMxRecord();
    }
}
