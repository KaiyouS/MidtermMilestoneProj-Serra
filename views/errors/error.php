<?php
/**
 * @var int $status
 * @var string $title
 * @var string $message
 */
?>
<section class="container narrow empty-state error-page">
    <p class="error-code"><?= e($status) ?></p>
    <h1><?= e($title) ?></h1>
    <p><?= e($message) ?></p>
    <a class="btn btn-primary" href="index.php">Back to recipes</a>
</section>
