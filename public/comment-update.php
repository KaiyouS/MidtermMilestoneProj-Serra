<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Repositories\CommentRepository;
use App\Validation\Validator;

$userId = $session->requireLogin();

if (!is_post()) {
    abort(405, 'Use the Edit button on your comment.');
}
$session->requireCsrf();

$comments = new CommentRepository($pdo);
$id = int_param($_POST, 'id');
$comment = $id ? $comments->find($id) : null;

if ($comment === null) {
    abort(404, 'That comment does not exist.');
}
if ((int) $comment['user_id'] !== $userId) {
    abort(403, 'You can only edit your own comments.');
}

$back = 'recipe.php?id=' . $comment['recipe_id'] . '#comment-' . $id;
$result = (new Validator())->comment($_POST);

if (!$result->passes()) {
    $session->flash('error', 'Comment not saved: ' . $result->errors()['body']);
} elseif ($result->data['body'] === $comment['body']) {
    $session->flash('info', 'No changes to save.');
} else {
    $comments->update($id, $userId, $result->data['body']);
    $session->flash('success', 'Comment updated.');
}

redirect($back);
