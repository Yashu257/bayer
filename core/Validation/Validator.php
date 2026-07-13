<?php

declare(strict_types=1);

namespace Core\Validation;

use Core\Exceptions\ValidationException;

/**
 * Validator — rule-based input validation.
 *
 * Usage:
 *   $v = new Validator($request->all(), [
 *       'email'    => 'required|email|max:180',
 *       'password' => 'required|min:8|password_strength',
 *   ]);
 *   $data = $v->validate();   // throws ValidationException on failure
 */
class Validator
{
    private array $errors = [];

    public function __construct(
        private readonly array $data,
        private readonly array $rules,
        private readonly array $messages = [],
    ) {}

    /**
     * Run all rules. Returns validated (trimmed) data on success.
     * Throws ValidationException if any rule fails.
     */
    public function validate(): array
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;

            foreach ($rules as $rule) {
                [$ruleName, $param] = $this->parseRule($rule);
                $this->applyRule($field, $value, $ruleName, $param);
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }

        // Return only validated fields, trimmed
        $validated = [];
        foreach (array_keys($this->rules) as $field) {
            $val = $this->data[$field] ?? null;
            $validated[$field] = is_string($val) ? trim($val) : $val;
        }
        return $validated;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    // --- Rule dispatcher -----------------------------------------------------

    private function applyRule(string $field, mixed $value, string $rule, ?string $param): void
    {
        $passed = match ($rule) {
            'required'         => $this->ruleRequired($value),
            'email'            => $this->ruleEmail($value),
            'min'              => $this->ruleMin($value, (int) $param),
            'max'              => $this->ruleMax($value, (int) $param),
            'confirmed'        => $this->ruleConfirmed($field, $value),
            'password_strength'=> $this->rulePasswordStrength($value),
            'alpha_num'        => $this->ruleAlphaNum($value),
            'numeric'          => is_numeric($value),
            'boolean'          => $this->ruleBoolean($value),
            'in'               => $this->ruleIn($value, $param ?? ''),
            'url'              => $this->ruleUrl($value),
            'date'             => $this->ruleDate($value),
            'nullable'         => true,   // always passes; stops further rules if null
            default            => throw new \InvalidArgumentException("Unknown validation rule: $rule"),
        };

        // nullable: skip remaining rules for this field if value is empty
        if ($rule === 'nullable' && ($value === null || $value === '')) {
            return;
        }

        if (!$passed) {
            $this->addError($field, $rule, $param);
        }
    }

    // --- Individual rules ----------------------------------------------------

    private function ruleRequired(mixed $value): bool
    {
        if (is_null($value))                         return false;
        if (is_string($value) && trim($value) === '') return false;
        if (is_array($value) && empty($value))        return false;
        return true;
    }

    private function ruleEmail(mixed $value): bool
    {
        if ($value === null || $value === '') return true;   // pair with required
        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    private function ruleMin(mixed $value, int $min): bool
    {
        if ($value === null || $value === '') return true;
        return is_numeric($value)
            ? (float) $value >= $min
            : mb_strlen((string) $value) >= $min;
    }

    private function ruleMax(mixed $value, int $max): bool
    {
        if ($value === null || $value === '') return true;
        return is_numeric($value)
            ? (float) $value <= $max
            : mb_strlen((string) $value) <= $max;
    }

    private function ruleConfirmed(string $field, mixed $value): bool
    {
        return $value === ($this->data["{$field}_confirmation"] ?? null);
    }

    private function rulePasswordStrength(mixed $value): bool
    {
        if (!is_string($value) || $value === '') return true;
        $config = require BASE_PATH . '/config/auth.php';
        $policy = $config['password'];

        if ($policy['require_uppercase'] && !preg_match('/[A-Z]/', $value))  return false;
        if ($policy['require_number']    && !preg_match('/[0-9]/', $value))  return false;
        if ($policy['require_special']   && !preg_match('/[\W_]/', $value))  return false;
        return true;
    }

    private function ruleAlphaNum(mixed $value): bool
    {
        if ($value === null || $value === '') return true;
        return (bool) preg_match('/^[a-zA-Z0-9]+$/', (string) $value);
    }

    private function ruleBoolean(mixed $value): bool
    {
        return in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true);
    }

    private function ruleIn(mixed $value, string $param): bool
    {
        if ($value === null || $value === '') return true;
        $allowed = explode(',', $param);
        return in_array($value, $allowed, true);
    }

    private function ruleUrl(mixed $value): bool
    {
        if ($value === null || $value === '') return true;
        return (bool) filter_var($value, FILTER_VALIDATE_URL);
    }

    private function ruleDate(mixed $value): bool
    {
        if ($value === null || $value === '') return true;
        return strtotime((string) $value) !== false;
    }

    // --- Error messages ------------------------------------------------------

    private function addError(string $field, string $rule, ?string $param): void
    {
        $customKey = "{$field}.{$rule}";
        if (isset($this->messages[$customKey])) {
            $this->errors[$field][] = $this->messages[$customKey];
            return;
        }

        $label = ucfirst(str_replace('_', ' ', $field));
        $this->errors[$field][] = match ($rule) {
            'required'          => "$label is required.",
            'email'             => "$label must be a valid email address.",
            'min'               => "$label must be at least $param characters.",
            'max'               => "$label must not exceed $param characters.",
            'confirmed'         => "$label confirmation does not match.",
            'password_strength' => "$label must contain uppercase, number, and special character.",
            'alpha_num'         => "$label may only contain letters and numbers.",
            'numeric'           => "$label must be a number.",
            'boolean'           => "$label must be true or false.",
            'in'                => "$label must be one of: $param.",
            'url'               => "$label must be a valid URL.",
            'date'              => "$label must be a valid date.",
            default             => "$label is invalid.",
        };
    }

    private function parseRule(string $rule): array
    {
        if (str_contains($rule, ':')) {
            [$name, $param] = explode(':', $rule, 2);
            return [$name, $param];
        }
        return [$rule, null];
    }
}
