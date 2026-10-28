<?php

declare(strict_types=1);

namespace App\Validation;

use App\Support\Quantity;

/**
 * Server-side validation for every form on the site. It only checks and cleans
 * input. It never touches the database or $_SESSION.
 */
final class Validator
{
    public function registration(array $input): ValidationResult
    {
        $data = [
            'display_name' => $this->line($input, 'display_name'),
            'email'        => mb_strtolower($this->line($input, 'email')),
        ];
        $password = $this->raw($input, 'password');
        $confirm = $this->raw($input, 'password_confirm');

        $errors = [];

        if ($error = $this->lengthError($data['display_name'], 'Name', Rules::NAME_MIN, Rules::NAME_MAX)) {
            $errors['display_name'] = $error;
        } elseif (!preg_match('/^' . Rules::NAME_PATTERN . '$/u', $data['display_name'])) {
            $errors['display_name'] = 'Name can only use letters, spaces, periods, apostrophes and hyphens.';
        }

        if ($error = $this->emailError($data['email'])) {
            $errors['email'] = $error;
        }

        $length = strlen($password);
        if ($length < Rules::PASSWORD_MIN || $length > Rules::PASSWORD_MAX) {
            $errors['password'] = sprintf('Password must be %d to %d characters.', Rules::PASSWORD_MIN, Rules::PASSWORD_MAX);
        } elseif (!preg_match('/^' . Rules::PASSWORD_PATTERN . '$/s', $password)) {
            $errors['password'] = 'Password needs at least one letter and one number.';
        }

        if (!isset($errors['password']) && $password !== $confirm) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }

        $data['password'] = $password;

        return new ValidationResult($data, $errors);
    }

    public function login(array $input): ValidationResult
    {
        $data = [
            'email'    => mb_strtolower($this->line($input, 'email')),
            'password' => $this->raw($input, 'password'),
        ];

        $errors = [];
        if ($data['email'] === '') {
            $errors['email'] = 'Enter your email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'That does not look like an email address.';
        }
        if ($data['password'] === '') {
            $errors['password'] = 'Enter your password.';
        }

        return new ValidationResult($data, $errors);
    }

    /** @param list<int> $categoryIds the categories that actually exist */
    public function recipe(array $input, array $categoryIds): ValidationResult
    {
        $data = [
            'title'       => $this->line($input, 'title'),
            'description' => $this->text($input, 'description'),
            'category_id' => filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT) ?: 0,
            'servings'    => $this->line($input, 'servings'),
            'steps'       => '',
            'ingredients' => [],
        ];
        $errors = [];

        if ($error = $this->lengthError($data['title'], 'Title', Rules::TITLE_MIN, Rules::TITLE_MAX)) {
            $errors['title'] = $error;
        }

        if (!in_array($data['category_id'], $categoryIds, true)) {
            $errors['category_id'] = 'Choose a category.';
        }

        if ($error = $this->lengthError($data['description'], 'Description', Rules::DESCRIPTION_MIN, Rules::DESCRIPTION_MAX)) {
            $errors['description'] = $error;
        }

        $servings = filter_var($data['servings'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => Rules::SERVINGS_MIN, 'max_range' => Rules::SERVINGS_MAX],
        ]);
        if ($servings === false) {
            $errors['servings'] = sprintf('Servings must be a whole number from %d to %d.', Rules::SERVINGS_MIN, Rules::SERVINGS_MAX);
        } else {
            $data['servings'] = $servings;
        }

        [$data['ingredients'], $ingredientErrors] = $this->ingredients($input);
        $errors += $ingredientErrors;

        [$data['steps'], $stepsError] = $this->steps($this->text($input, 'steps'));
        if ($stepsError !== null) {
            $errors['steps'] = $stepsError;
        }

        return new ValidationResult($data, $errors);
    }

    public function comment(array $input): ValidationResult
    {
        $data = ['body' => $this->text($input, 'body')];
        $errors = [];

        if ($error = $this->lengthError($data['body'], 'Comment', Rules::COMMENT_MIN, Rules::COMMENT_MAX)) {
            $errors['body'] = $error;
        }

        return new ValidationResult($data, $errors);
    }

    // ---- Recipe pieces ------------------------------------------------------

    /**
     * The form sends three parallel arrays (quantity[], unit[], name[]), one entry per row.
     * Completely blank rows are ignored; each kept row is validated on its own.
     *
     * @return array{0: list<array{quantity_text: string, quantity: ?float, unit: string, name: string}>, 1: array<string, string>}
     */
    private function ingredients(array $input): array
    {
        $quantities = $this->list($input, 'ingredient_quantity');
        $units = $this->list($input, 'ingredient_unit');
        $names = $this->list($input, 'ingredient_name');

        $rows = [];
        $errors = [];
        $count = max(count($quantities), count($units), count($names));

        for ($i = 0; $i < $count; $i++) {
            $row = [
                'quantity_text' => $this->clean($quantities[$i] ?? ''),
                'quantity'      => null,
                'unit'          => $this->clean($units[$i] ?? ''),
                'name'          => $this->clean($names[$i] ?? ''),
            ];

            if ($row['quantity_text'] === '' && $row['unit'] === '' && $row['name'] === '') {
                continue;
            }

            $index = count($rows);
            $problems = [];

            if ($row['name'] === '') {
                $problems[] = 'name the ingredient';
            } elseif (mb_strlen($row['name']) > Rules::INGREDIENT_NAME_MAX) {
                $problems[] = sprintf('keep the name under %d characters', Rules::INGREDIENT_NAME_MAX);
            }

            if ($row['quantity_text'] !== '') {
                $row['quantity'] = Quantity::parse($row['quantity_text']);
                if ($row['quantity'] === null) {
                    $problems[] = 'use an amount like 2, 1.5, 1/2 or 1 1/2';
                }
            } elseif ($row['unit'] !== '') {
                $problems[] = 'add an amount for the unit';
            }

            if (mb_strlen($row['unit']) > Rules::UNIT_MAX) {
                $problems[] = sprintf('keep the unit under %d characters', Rules::UNIT_MAX);
            }

            if ($problems) {
                $errors["ingredients.{$index}"] = ucfirst(implode(', ', $problems)) . '.';
            }

            $rows[] = $row;
        }

        if (count($rows) < Rules::INGREDIENTS_MIN) {
            $errors['ingredients'] = 'Add at least one ingredient.';
        } elseif (count($rows) > Rules::INGREDIENTS_MAX) {
            $errors['ingredients'] = sprintf('A recipe can have at most %d ingredients.', Rules::INGREDIENTS_MAX);
        }

        return [$rows, $errors];
    }

    /**
     * One step per line. Leading numbers like "1." or "2)" are removed because
     * the recipe page numbers the steps itself.
     *
     * @return array{0: string, 1: ?string}
     */
    private function steps(string $text): array
    {
        $steps = [];
        foreach (explode("\n", $text) as $line) {
            $line = trim(preg_replace('/^\s*(?:step\s*)?\d+\s*[.):-]\s*/iu', '', $line));
            if ($line !== '') {
                $steps[] = $line;
            }
        }
        $normalized = implode("\n", $steps);

        if ($steps === [] || mb_strlen($normalized) < Rules::STEPS_MIN_CHARS) {
            return [$normalized, sprintf('Write the cooking steps (at least %d characters).', Rules::STEPS_MIN_CHARS)];
        }
        if (mb_strlen($normalized) > Rules::STEPS_MAX_CHARS) {
            return [$normalized, sprintf('Steps must be under %d characters in total.', Rules::STEPS_MAX_CHARS)];
        }
        if (count($steps) > Rules::STEPS_MAX_COUNT) {
            return [$normalized, sprintf('Keep it to %d steps or fewer.', Rules::STEPS_MAX_COUNT)];
        }

        return [$normalized, null];
    }

    // ---- Small helpers --------------------------------------------------------

    private function emailError(string $email): ?string
    {
        if ($email === '') {
            return 'Enter your email.';
        }
        if (mb_strlen($email) > Rules::EMAIL_MAX || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Enter a valid email address.';
        }
        return null;
    }

    private function lengthError(string $value, string $label, int $min, int $max): ?string
    {
        $length = mb_strlen($value);
        if ($length === 0) {
            return "{$label} is required.";
        }
        if ($length < $min || $length > $max) {
            return "{$label} must be {$min} to {$max} characters.";
        }
        return null;
    }

    /**
     * A value exactly as typed (passwords must not be trimmed).
     * Non-strings (tampered arrays) and invalid UTF-8 become ''.
     */
    private function raw(array $input, string $key): string
    {
        $value = $input[$key] ?? null;
        return is_string($value) && mb_check_encoding($value, 'UTF-8') ? $value : '';
    }

    /** Single-line text: trimmed, inner whitespace collapsed. */
    private function line(array $input, string $key): string
    {
        return $this->clean($this->raw($input, $key));
    }

    /** Multi-line text: trimmed, Windows line endings normalized, runs of blank lines squeezed. */
    private function text(array $input, string $key): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $this->raw($input, $key));
        return trim(preg_replace("/\n{3,}/", "\n\n", $value));
    }

    private function clean(mixed $value): string
    {
        if (!is_string($value) || !mb_check_encoding($value, 'UTF-8')) {
            return '';
        }
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    private function list(array $input, string $key): array
    {
        return is_array($input[$key] ?? null) ? array_values($input[$key]) : [];
    }
}
