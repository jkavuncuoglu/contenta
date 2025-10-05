<?php

namespace App\Domains\Security\Authentication\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password as PasswordRule;

class StrongPassword implements ValidationRule
{
    const int MIN_PASSWORD_LENGTH = 12;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('The :attribute must be a string.');
            return;
        }

        $password = trim($value);

        // Basic length requirement
        $isLongEnough = mb_strlen($password) >= self::MIN_PASSWORD_LENGTH;

        // Passphrase: at least 3 words (letters-only sequences) and 12+ characters
        $wordCount = preg_match_all('/\p{L}+/u', $password, $matches);
        $isPassphrase = $isLongEnough && $wordCount !== false && $wordCount >= 3;

        // Mixed-character (random-like) password: 12+ with upper, lower, digit, and special
        $hasLower = (bool) preg_match('/[a-z]/', $password);
        $hasUpper = (bool) preg_match('/[A-Z]/', $password);
        $hasDigit = (bool) preg_match('/\d/', $password);
        $hasSpecial = (bool) preg_match('/[^a-zA-Z0-9]/', $password);
        $isRandomStrong = $isLongEnough && $hasLower && $hasUpper && $hasDigit && $hasSpecial;

        // If neither strategy is satisfied, it's weak
        if (!($isPassphrase || $isRandomStrong)) {
            $fail('The :attribute is too weak. Use a passphrase (12+ chars, 3+ words) or a 12+ character password with upper, lower, number, and symbol.');
            return;
        }

        // Check if password is compromised using Have I Been Pwned API
        if (PasswordRule::uncompromised()->passes('password', $password) === false) {
            $fail('The :attribute has been found in a data breach. Please choose a different password.');
            return;
        }
    }
}
