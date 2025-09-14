<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SecureInput implements ValidationRule
{
    /**
     * Dangerous patterns to check for
     */
    protected array $dangerousPatterns = [
        // SQL Injection patterns
        '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',

        // XSS patterns
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi',
        '/javascript:/i',
        '/on\w+\s*=/i',

        // Path traversal
        '/\.\.\//i',
        '/\.\.\\/i',

        // Command injection
        '/[;&|`$(){}]/i',

        // PHP code injection
        '/<\?php/i',
        '/eval\s*\(/i',
        '/exec\s*\(/i',
        '/system\s*\(/i',
        '/shell_exec\s*\(/i',
        '/passthru\s*\(/i',
        '/file_get_contents\s*\(/i',
        '/file_put_contents\s*\(/i',
        '/fopen\s*\(/i',
        '/fwrite\s*\(/i',
        '/include\s*\(/i',
        '/require\s*\(/i',
    ];

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        // Check for dangerous patterns
        foreach ($this->dangerousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $fail("حقل {$attribute} يحتوي على محتوى غير آمن.");
                return;
            }
        }

        // Check for excessive length (potential DoS)
        if (strlen($value) > 10000) {
            $fail("حقل {$attribute} طويل جداً.");
            return;
        }

        // Check for null bytes
        if (strpos($value, "\0") !== false) {
            $fail("حقل {$attribute} يحتوي على أحرف غير صالحة.");
            return;
        }

        // Check for control characters (except common ones like \n, \r, \t)
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
            $fail("حقل {$attribute} يحتوي على أحرف تحكم غير مسموحة.");
            return;
        }
    }
}