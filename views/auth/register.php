<?php

use App\Validation\Rules;

/**
 * @var array<string, string> $errors
 * @var array $old
 */
?>
<section class="auth-wrap">
    <div class="auth-intro">
        <p class="eyebrow">Tuloy po kayo!</p>
        <h1>Join the barangay kitchen</h1>
        <p>An account lets you post recipes, leave tips for other cooks, and keep a list of favorites to try this week.</p>
        <ul class="auth-perks">
            <li>Share your lola's recipes before they're forgotten</li>
            <li>Scale any recipe to the number of people you're feeding</li>
            <li>Only you can edit what you post</li>
        </ul>
    </div>

    <form class="card auth-card" method="post" action="register.php" data-validate>
        <input type="hidden" name="_token" value="<?= e($session->csrfToken()) ?>">

        <h2>Create an account</h2>

        <div class="field">
            <label for="display_name">Your name</label>
            <input id="display_name" name="display_name" type="text" autocomplete="name" required data-label="Name"
                   minlength="<?= Rules::NAME_MIN ?>" maxlength="<?= Rules::NAME_MAX ?>"
                   pattern="<?= e(Rules::NAME_PATTERN) ?>"
                   data-pattern-message="Use letters, spaces, periods, apostrophes and hyphens only."
                   value="<?= e($old['display_name'] ?? '') ?>"
                   <?= isset($errors['display_name']) ? 'aria-invalid="true"' : '' ?>
                   aria-describedby="display_name-hint display_name-error">
            <p class="field-hint" id="display_name-hint">This is shown on your recipes and comments, e.g. "Aling Nena".</p>
            <p class="field-error" id="display_name-error"><?= e($errors['display_name'] ?? '') ?></p>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required
                   maxlength="<?= Rules::EMAIL_MAX ?>"
                   value="<?= e($old['email'] ?? '') ?>"
                   <?= isset($errors['email']) ? 'aria-invalid="true"' : '' ?>
                   aria-describedby="email-error">
            <p class="field-error" id="email-error"><?= e($errors['email'] ?? '') ?></p>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required
                   minlength="<?= Rules::PASSWORD_MIN ?>" maxlength="<?= Rules::PASSWORD_MAX ?>"
                   pattern="<?= e(Rules::PASSWORD_PATTERN) ?>"
                   data-pattern-message="Use at least one letter and one number."
                   <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>
                   aria-describedby="password-hint password-error">
            <p class="field-hint" id="password-hint"><?= Rules::PASSWORD_MIN ?> or more characters, with at least one letter and one number.</p>
            <p class="field-error" id="password-error"><?= e($errors['password'] ?? '') ?></p>
        </div>

        <div class="field">
            <label for="password_confirm">Confirm password</label>
            <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required
                   data-match="password" data-match-message="Passwords do not match."
                   <?= isset($errors['password_confirm']) ? 'aria-invalid="true"' : '' ?>
                   aria-describedby="password_confirm-error">
            <p class="field-error" id="password_confirm-error"><?= e($errors['password_confirm'] ?? '') ?></p>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Create account</button>
        <p class="auth-switch">Already a member? <a href="login.php">Log in</a></p>
    </form>
</section>
