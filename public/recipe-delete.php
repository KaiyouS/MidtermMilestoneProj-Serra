<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Repositories\RecipeRepository;

$userId = $session->requireLogin();

if (!is_post()) {
    abort(405, 'Use the Delete button on the recipe page.');
}
$session->requireCsrf();

$id = int_param($_POST, 'id') ?? abort(404, 'That recipe does not exist.');

// The repository only deletes when both the ID and the owner match.
if (!(new RecipeRepository($pdo))->delete($id, $userId)) {
    abort(403, 'You can only delete recipes that you shared.');
}

$session->flash('success', 'Recipe deleted.');
redirect('index.php?mine=1');
