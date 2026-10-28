<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Repositories\CommentRepository;

$userId = $session->requireLogin();

if (!is_post()) {
    abort(405, 'Use the Delete button on your comment.');
}
$session->requireCsrf();

$comments = new CommentRepository($pdo);
$id = int_param($_POST, 'id');
$comment = $id ? $comments->find($id) : null;

if ($comment === null) {
    abort(404, 'That comment does not exist.');
}
if (!$comments->delete($id, $userId)) {
    abort(403, 'You can only delete your own comments.');
}

$session->flash('success', 'Comment deleted.');
redirect('recipe.php?id=' . $comment['recipe_id'] . '#comments');
