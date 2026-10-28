<?php
/**
 * @var App\Core\SessionManager $session
 * @var string $content
 * @var string $title
 * @var string|null $activeNav
 */
$activeNav ??= '';
$navItems = [
    'browse'    => ['index.php', 'Recipes'],
    'mine'      => ['index.php?mine=1', 'My recipes'],
    'favorites' => ['favorites.php', 'Favorites'],
];
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Lutong Bahay</title>
    <meta name="description" content="Lutong Bahay: home-cooked recipes shared by our barangay's cooks.">
    <link rel="icon" href="assets/img/palayok.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/app.css">
    <script src="assets/js/app.js"></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">
            <img src="assets/img/palayok.svg" alt="" width="36" height="36">
            <span class="brand-text">
                <span class="brand-name">Lutong Bahay</span>
                <span class="brand-tag">Barangay recipe exchange</span>
            </span>
        </a>

        <?php if ($session->isLoggedIn()): ?>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
                <span class="nav-toggle-bar"></span>
                <span class="visually-hidden">Menu</span>
            </button>
            <nav id="site-nav" class="site-nav" aria-label="Main">
                <ul class="nav-list">
                    <?php foreach ($navItems as $key => [$href, $label]): ?>
                        <li>
                            <a href="<?= e($href) ?>" <?= $activeNav === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a class="btn btn-primary btn-sm nav-cta" href="recipe-create.php">+ Share a recipe</a>
                <div class="nav-user">
                    <span class="nav-hello">Hi, <strong><?= e($session->userName()) ?></strong></span>
                    <form method="post" action="logout.php">
                        <input type="hidden" name="_token" value="<?= e($session->csrfToken()) ?>">
                        <button class="link-button" type="submit">Log out</button>
                    </form>
                </div>
            </nav>
        <?php else: ?>
            <nav class="guest-nav" aria-label="Account">
                <a href="login.php" <?= $activeNav === 'login' ? 'aria-current="page"' : '' ?>>Log in</a>
                <a class="btn btn-primary btn-sm" href="register.php">Join</a>
            </nav>
        <?php endif; ?>
    </div>
</header>

<main id="main" class="site-main">
    <?php $flashes = $session->flashes(); ?>
    <?php if ($flashes): ?>
        <div class="container flash-stack">
            <?php foreach ($flashes as $flash): ?>
                <div class="flash flash-<?= e($flash['type']) ?>" role="status">
                    <span><?= e($flash['message']) ?></span>
                    <button type="button" class="flash-close" aria-label="Dismiss">×</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <p><strong>Lutong Bahay</strong> · Luto ng bayan, para sa bayan.</p>
        <p class="muted">Made for our barangay's home cooks. Share kindly. Cook generously.</p>
    </div>
</footer>

<div class="toast" role="status" aria-live="polite" hidden></div>
</body>
</html>
