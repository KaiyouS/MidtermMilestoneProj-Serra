<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Core\View;
use App\Repositories\CommentRepository;
use App\Repositories\RecipeRepository;

$userId = $session->requireLogin();

$id = int_param($_GET, 'id') ?? abort(404, 'That recipe does not exist.');

$recipes = new RecipeRepository($pdo);
$recipe = $recipes->find($id, $userId) ?? abort(404, 'That recipe does not exist or was removed by its cook.');

View::render('recipes/show', [
    'title'       => $recipe['title'],
    'recipe'      => $recipe,
    'ingredients' => $recipes->ingredients($id),
    'steps'       => explode("\n", $recipe['steps']),
    'comments'    => (new CommentRepository($pdo))->forRecipe($id),
]);
