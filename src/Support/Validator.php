<?php

declare(strict_types=1);

namespace SiteForgeAI\Support;

class Validator
{
    private const TEXT_DOMAIN = 'siteforge-ai';

    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data  = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function validate(): array
    {
        foreach ($this->rules as $field => $rule_definition) {
            $rules = is_array($rule_definition)
                ? $rule_definition
                : explode('|', $rule_definition);

            $value  = $this->data[$field] ?? null;
            $exists = array_key_exists($field, $this->data);

            // If 'sometimes' or 'nullable' and field is missing or null, skip
            if ((in_array('sometimes', $rules, true) && !$exists) ||
                (in_array('nullable', $rules, true) && $value === null)) {
                continue;
            }

            foreach ($rules as $rule) {
                if ($rule === 'sometimes' || $rule === 'nullable') {
                    continue;
                }

                $this->applyRule($field, $value, $rule);
            }
        }

        return $this->errors;
    }

    public function passes(): bool
    {
        return empty($this->validate());
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors)[0] : null;
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        $params = [];
        if (str_contains($rule, ':')) {
            [$rule, $param_str] = explode(':', $rule, 2);
            $params = explode(',', $param_str);
        }

        $error = match ($rule) {
            'required' => ($value === null || $value === '' || (is_array($value) && empty($value)))
                ? sprintf(__('The %s field is required.', self::TEXT_DOMAIN), $field)
                : null,

            'string'   => ($value !== null && !is_string($value))
                ? sprintf(__('The %s must be a string.', self::TEXT_DOMAIN), $field)
                : null,

            'numeric'  => ($value !== null && !is_numeric($value))
                ? sprintf(__('The %s must be a number.', self::TEXT_DOMAIN), $field)
                : null,

            'integer'  => ($value !== null && filter_var($value, FILTER_VALIDATE_INT) === false)
                ? sprintf(__('The %s must be an integer.', self::TEXT_DOMAIN), $field)
                : null,

            'boolean'  => ($value !== null && !is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true))
                ? sprintf(__('The %s must be a boolean.', self::TEXT_DOMAIN), $field)
                : null,

            'array'    => ($value !== null && !is_array($value))
                ? sprintf(__('The %s must be an array.', self::TEXT_DOMAIN), $field)
                : null,

            'in'       => ($value !== null && !in_array((string) $value, $params, true))
                ? sprintf(__('The selected %s is invalid. Must be one of: %s.', self::TEXT_DOMAIN), $field, implode(', ', $params))
                : null,

            'min'      => $this->validateMin($field, $value, (float) ($params[0] ?? 0)),

            'max'      => $this->validateMax($field, $value, (float) ($params[0] ?? 0)),

            default    => null,
        };

        if ($error !== null) {
            $this->addError($field, $error);
        }
    }

    private function validateMin(string $field, mixed $value, float $min): ?string
    {
        if (is_string($value) && mb_strlen($value) < $min) {
            return sprintf(__('The %s must be at least %d characters.', self::TEXT_DOMAIN), $field, (int) $min);
        }
        if (is_numeric($value) && (float) $value < $min) {
            return sprintf(__('The %s must be at least %s.', self::TEXT_DOMAIN), $field, (string) $min);
        }
        return null;
    }

    private function validateMax(string $field, mixed $value, float $max): ?string
    {
        if (is_string($value) && mb_strlen($value) > $max) {
            return sprintf(__('The %s may not be greater than %d characters.', self::TEXT_DOMAIN), $field, (int) $max);
        }
        if (is_numeric($value) && (float) $value > $max) {
            return sprintf(__('The %s may not be greater than %s.', self::TEXT_DOMAIN), $field, (string) $max);
        }
        return null;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
