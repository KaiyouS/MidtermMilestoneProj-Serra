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

if (is_post()) {
    $session->requireCsrf();

    $result = (new Validator())->registration($_POST);
    $old = ['display_name' => $result->data['display_name'], 'email' => $result->data['email']];
    $users = new UserRepository($pdo);

    if (!isset($result->errors()['email']) && $users->emailExists($result->data['email'])) {
        $result->addError('email', 'An account with this email already exists. Try logging in.');
    }

    if ($result->passes()) {
        $id = $users->create($result->data['display_name'], $result->data['email'], $result->data['password']);
        $session->login(['id' => $id, 'display_name' => $result->data['display_name']]);
        $session->flash('success', 'Welcome to Lutong Bahay, ' . $result->data['display_name'] . '! Start by browsing or sharing a recipe.');
        redirect('index.php');
    }

    $errors = $result->errors();
}

View::render('auth/register', [
    'title'  => 'Create an account',
    'errors' => $errors,
    'old'    => $old,
], $errors ? 422 : 200);
