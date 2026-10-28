<?php

use App\Core\View;

/**
 * @var array $recipe
 * @var string $next
 */
?>
<article class="card recipe-card" data-recipe-card="<?= e($recipe['id']) ?>">
    <div class="recipe-card-top">
        <span class="tag tag-cat-<?= e($recipe['category_id']) ?>"><?= e($recipe['category']) ?></span>
        <?php if ((int) $recipe['user_id'] === $session->userId()): ?>
            <span class="tag tag-mine">Yours</span>
        <?php endif; ?>
    </div>

    <h2 class="recipe-card-title">
        <a href="recipe.php?id=<?= e($recipe['id']) ?>"><?= e($recipe['title']) ?></a>
    </h2>
    <p class="recipe-card-desc"><?= e($recipe['description']) ?></p>

    <p class="recipe-meta">
        by <strong><?= e($recipe['author']) ?></strong>
        · <time datetime="<?= e($recipe['created_at']) ?>"><?= e(format_date($recipe['created_at'])) ?></time>
        <?php if ($recipe['updated_at']): ?>
            · <span class="edited" title="Edited <?= e(format_datetime($recipe['updated_at'])) ?>">edited</span>
        <?php endif; ?>
    </p>

    <div class="recipe-card-foot">
        <span class="stat" title="Serves <?= e($recipe['servings']) ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12h18a9 7 0 0 1-18 0zM8 9c0-1.5 1-1.5 1-3m3 3c0-1.5 1-1.5 1-3m3 3c0-1.5 1-1.5 1-3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?= e($recipe['servings']) ?>
            <span class="visually-hidden">servings</span>
        </span>
        <a class="stat" href="recipe.php?id=<?= e($recipe['id']) ?>#comments" title="Comments">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v10H9l-5 4z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
            <?= e($recipe['comment_count']) ?>
            <span class="visually-hidden">comments</span>
        </a>
        <?php View::partial('partials/favorite-button', ['recipe' => $recipe, 'next' => $next, 'compact' => true]); ?>
    </div>
</article>
