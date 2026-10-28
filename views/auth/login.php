<?php
/**
 * @var array<string, string> $errors
 * @var array $old
 * @var string $next
 */
?>
<section class="auth-wrap">
    <div class="auth-intro">
        <p class="eyebrow">Kain tayo!</p>
        <h1>Welcome back to the kitchen</h1>
        <p>Log in to see what your neighbors are cooking, save the dishes you want to try, and share your own family recipes.</p>
    </div>

    <form class="card auth-card" method="post" action="login.php" data-validate>
        <input type="hidden" name="_token" value="<?= e($session->csrfToken()) ?>">
        <input type="hidden" name="next" value="<?= e($next) ?>">

        <h2>Log in</h2>

        <?php if (isset($errors['login'])): ?>
            <p class="form-alert" role="alert"><?= e($errors['login']) ?></p>
        <?php endif; ?>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required
                   maxlength="<?= App\Validation\Rules::EMAIL_MAX ?>"
                   value="<?= e($old['email'] ?? '') ?>"
                   <?= isset($errors['email']) ? 'aria-invalid="true"' : '' ?>
                   aria-describedby="email-error">
            <p class="field-error" id="email-error"><?= e($errors['email'] ?? '') ?></p>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>
                   aria-describedby="password-error">
            <p class="field-error" id="password-error"><?= e($errors['password'] ?? '') ?></p>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Log in</button>
        <p class="auth-switch">New here? <a href="register.php">Create an account</a></p>
    </form>
</section>
