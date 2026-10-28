<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Core\View;
use App\Repositories\CategoryRepository;
use App\Repositories\RecipeRepository;
use App\Validation\Validator;

$userId = $session->requireLogin();

$categories = new CategoryRepository($pdo);
$errors = [];
$form = [
    'title'       => '',
    'category_id' => (int) ($_GET['category'] ?? 0),
    'description' => '',
    'servings'    => 4,
    'steps'       => '',
    'ingredients' => [],
];

if (is_post()) {
    $session->requireCsrf();

    $result = (new Validator())->recipe($_POST, $categories->ids());

    if ($result->passes()) {
        $id = (new RecipeRepository($pdo))->create($userId, $result->data);
        $session->flash('success', 'Your recipe is live. Salamat sa pag-share!');
        redirect('recipe.php?id=' . $id);
    }

    $form = $result->data;
    $errors = $result->errors();
}

View::render('recipes/form', [
    'title'      => 'Share a recipe',
    'editing'    => false,
    'form'       => $form,
    'errors'     => $errors,
    'categories' => $categories->all(),
], $errors ? 422 : 200);
