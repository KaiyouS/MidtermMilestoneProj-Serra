<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Every validation limit in one place. The Validator enforces these on the
 * server, and the form views print the same numbers into HTML attributes
 * (required, minlength, maxlength, min, max, pattern) for the browser side.
 */
final class Rules
{
    public const NAME_MIN = 2;
    public const NAME_MAX = 60;
    /** Letters (any language), spaces, periods, apostrophes and hyphens. Must start with a letter. */
    public const NAME_PATTERN = "\p{L}[\p{L} .'\-]*";

    public const EMAIL_MAX = 190;

    public const PASSWORD_MIN = 8;
    public const PASSWORD_MAX = 72; // bcrypt only uses the first 72 bytes
    public const PASSWORD_PATTERN = '(?=.*[A-Za-z])(?=.*\d).+';

    public const TITLE_MIN = 3;
    public const TITLE_MAX = 120;

    public const DESCRIPTION_MIN = 10;
    public const DESCRIPTION_MAX = 500;

    public const SERVINGS_MIN = 1;
    public const SERVINGS_MAX = 50;

    public const INGREDIENTS_MIN = 1;
    public const INGREDIENTS_MAX = 40;
    public const INGREDIENT_NAME_MAX = 100;
    public const UNIT_MAX = 30;

    public const STEPS_MIN_CHARS = 10;
    public const STEPS_MAX_CHARS = 5000;
    public const STEPS_MAX_COUNT = 30;

    public const COMMENT_MIN = 2;
    public const COMMENT_MAX = 1000;

    public const SEARCH_MAX = 60;
}
