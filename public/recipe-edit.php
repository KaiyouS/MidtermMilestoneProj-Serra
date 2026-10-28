<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Core\View;
use App\Repositories\CategoryRepository;
use App\Repositories\RecipeRepository;
use App\Support\Quantity;
use App\Validation\Validator;

$userId = $session->requireLogin();

$id = int_param($_GET, 'id') ?? abort(404, 'That recipe does not exist.');
$recipes = new RecipeRepository($pdo);
$recipe = $recipes->find($id, $userId) ?? abort(404, 'That recipe does not exist.');

if ((int) $recipe['user_id'] !== $userId) {
    abort(403, 'You can only edit recipes that you shared.');
}

$categories = new CategoryRepository($pdo);
$errors = [];

// Current values, shaped like the Validator's output so the same form can show either.
$current = [
    'id'          => $id,
    'title'       => $recipe['title'],
    'category_id' => (int) $recipe['category_id'],
    'description' => $recipe['description'],
    'servings'    => (int) $recipe['servings'],
    'steps'       => $recipe['steps'],
    'ingredients' => array_map(static fn (array $row): array => [
        'quantity_text' => $row['quantity'] === null ? '' : Quantity::format((float) $row['quantity'], true),
        'quantity'      => $row['quantity'] === null ? null : (float) $row['quantity'],
        'unit'          => $row['unit'],
        'name'          => $row['name'],
    ], $recipes->ingredients($id)),
];
$form = $current;

/** Compare only what gets saved, so re-saving an untouched form doesn't mark the recipe "edited". */
$comparable = static fn (array $data): array => [
    $data['title'], $data['category_id'], $data['description'], $data['servings'], $data['steps'],
    array_map(static fn (array $i): array => [$i['quantity'], $i['unit'], $i['name']], $data['ingredients']),
];

if (is_post()) {
    $session->requireCsrf();

    $result = (new Validator())->recipe($_POST, $categories->ids());

    if ($result->passes()) {
        if ($comparable($result->data) === $comparable($current)) {
            $session->flash('info', 'No changes to save.');
        } elseif ($recipes->update($id, $userId, $result->data)) {
            $session->flash('success', 'Recipe updated.');
        } else {
            abort(403, 'You can only edit recipes that you shared.');
        }
        redirect('recipe.php?id=' . $id);
    }

    $form = ['id' => $id] + $result->data;
    $errors = $result->errors();
}

View::render('recipes/form', [
    'title'      => 'Edit ' . $recipe['title'],
    'editing'    => true,
    'form'       => $form,
    'errors'     => $errors,
    'categories' => $categories->all(),
], $errors ? 422 : 200);
