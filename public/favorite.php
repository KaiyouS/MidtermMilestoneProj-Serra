<?php

declare(strict_types=1);

/*
 * Save or unsave a recipe.
 * app.js calls this with fetch() and gets JSON back, so the page never reloads.
 * Without JavaScript the same form posts normally and we redirect back.
 */

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Repositories\FavoriteRepository;
use App\Repositories\RecipeRepository;

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

$respond = static function (int $status, array $body) use ($wantsJson, $session): never {
    if (!$wantsJson) {
        if ($status >= 400) {
            abort($status, $body['error']);
        }
        $session->flash('success', $body['favorited'] ? 'Saved to your favorites.' : 'Removed from your favorites.');
        redirect(safe_next($_POST['next'] ?? null));
    }

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body);
    exit;
};

if (!is_post()) {
    $respond(405, ['error' => 'Use POST.']);
}
if (!$session->isLoggedIn()) {
    $respond(401, ['error' => 'Please log in again.']);
}
if (!$session->isValidCsrf($_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    $respond(419, ['error' => 'Your session expired. Refresh the page and try again.']);
}

$userId = $session->userId();
$recipeId = int_param($_POST, 'recipe_id');
$mode = $_POST['mode'] ?? '';

if ($recipeId === null || !(new RecipeRepository($pdo))->exists($recipeId)) {
    $respond(404, ['error' => 'That recipe no longer exists.']);
}
if (!in_array($mode, ['add', 'remove'], true)) {
    $respond(422, ['error' => 'Unknown action.']);
}

$favorites = new FavoriteRepository($pdo);
$mode === 'add' ? $favorites->add($userId, $recipeId) : $favorites->remove($userId, $recipeId);

// Report what is actually stored, not what was asked for.
$respond(200, [
    'favorited' => $favorites->isFavorite($userId, $recipeId),
    'count'     => $favorites->countFor($recipeId),
]);
