<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Repositories\CommentRepository;
use App\Repositories\RecipeRepository;
use App\Validation\Validator;

$userId = $session->requireLogin();

if (!is_post()) {
    abort(405, 'Use the comment form on a recipe page.');
}
$session->requireCsrf();

$recipeId = int_param($_POST, 'recipe_id');
if ($recipeId === null || !(new RecipeRepository($pdo))->exists($recipeId)) {
    abort(404, 'That recipe does not exist.');
}

$result = (new Validator())->comment($_POST);

if (!$result->passes()) {
    $session->flash('error', $result->errors()['body']);
    $session->flashOld(['body' => $result->data['body']]);
    redirect('recipe.php?id=' . $recipeId . '#comments');
}

$commentId = (new CommentRepository($pdo))->create($recipeId, $userId, $result->data['body']);
$session->flash('success', 'Comment posted.');
redirect('recipe.php?id=' . $recipeId . '#comment-' . $commentId);
