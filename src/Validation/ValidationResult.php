<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * What the Validator hands back: the cleaned-up values and any error messages,
 * keyed by field name so the form can show each message under its input.
 */
final class ValidationResult
{
    /** @param array<string, string> $errors */
    public function __construct(
        public readonly array $data,
        private array $errors = [],
    ) {
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** For checks only the page can do, like "this email is already registered". */
    public function addError(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }
}
