<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

// Logging out changes state, so it must be a POST with a valid token
// (a plain link could be triggered by another site).
if (!is_post()) {
    redirect('index.php');
}
$session->requireCsrf();

$session->logout();

// Start a fresh, empty session just to carry the goodbye message.
$session = new App\Core\SessionManager();
$session->start();
$session->flash('success', 'You have logged out. Ingat!');

redirect('login.php');
