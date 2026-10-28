<?php

declare(strict_types=1);

/*
 * The one and only database connection file.
 * bootstrap.php requires this once and every page gets the same $pdo from it.
 *
 * The defaults below match a fresh Herd / XAMPP install (root, no password).
 * To use different credentials without committing them, copy
 * database.local.example.php to database.local.php (git-ignored) and edit it.
 */

$db = [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'name'     => 'lutong_bahay',
    'user'     => 'root',
    'password' => '',
];

$localConfig = __DIR__ . '/database.local.php';
if (is_file($localConfig)) {
    $db = array_merge($db, require $localConfig);
}

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], $db['port'], $db['name']);

try {
    $pdo = new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Real server-side prepared statements: values never get spliced into the SQL text.
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    // Keep NOW() / CURRENT_TIMESTAMP in Philippine time, same as PHP.
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Could not connect to the database. Check the settings in config/database.php.');
}

unset($db, $dsn, $localConfig);

return $pdo;
