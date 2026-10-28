<?php

declare(strict_types=1);

/*
 * Every page in public/ starts with: require __DIR__ . '/../bootstrap.php';
 * It loads the classes, connects to the database, and starts the session.
 */

use App\Core\SessionManager;
use App\Core\View;
use App\Repositories\UserRepository;

date_default_timezone_set('Asia/Manila');

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    exit('Run "composer dump-autoload" in the project folder first.');
}
require $autoload;

/** @var PDO $pdo */
$pdo = require __DIR__ . '/config/database.php';

$session = new SessionManager();
$session->start();

// A session can outlive its account (e.g. the member was removed from the database).
// Treat it as logged out instead of letting writes fail against a missing user.
if ($session->isLoggedIn() && !(new UserRepository($pdo))->exists($session->userId())) {
    $session->logout();
    $session = new SessionManager();
    $session->start();
}

View::setDefaults(__DIR__ . '/views', $session);

// Basic hardening headers. The CSP only allows our own scripts/styles, which
// also blocks injected inline <script> even if escaping were ever missed.
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
