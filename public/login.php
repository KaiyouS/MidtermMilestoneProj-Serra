<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Core\View;
use App\Repositories\UserRepository;
use App\Validation\Validator;

$session->requireGuest();

$errors = [];
$old = [];
$next = safe_next($_POST['next'] ?? $_GET['next'] ?? null);

if (is_post()) {
    $session->requireCsrf();

    $result = (new Validator())->login($_POST);
    $old = ['email' => $result->data['email']];

    if ($result->passes()) {
        $users = new UserRepository($pdo);
        $user = $users->findByEmail($result->data['email']);

        if ($user && password_verify($result->data['password'], $user['password_hash'])) {
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $users->updatePasswordHash((int) $user['id'], $result->data['password']);
            }
            $session->login($user);
            $session->flash('success', 'Welcome back, ' . $user['display_name'] . '!');
            redirect($next);
        }

        // Same message whether the email or the password was wrong.
        $result->addError('login', 'That email and password combination is not right.');
    }

    $errors = $result->errors();
}

View::render('auth/login', [
    'title'     => 'Log in',
    'activeNav' => 'login',
    'errors'    => $errors,
    'old'       => $old,
    'next'      => $next,
], $errors ? 422 : 200);
