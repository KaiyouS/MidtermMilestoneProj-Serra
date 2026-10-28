<?php

use App\Core\View;

/**
 * @var list<array> $recipes
 * @var int $total
 * @var int $page
 * @var int $pages
 */
?>
<section class="hero hero-compact">
    <div class="container">
        <p class="eyebrow">Lulutuin ko 'to</p>
        <h1>My favorites</h1>
        <p class="hero-lead">Recipes you've saved to try. Tap the heart to remove one.</p>
    </div>
</section>

<section class="container" data-favorites-page>
    <?php if ($recipes): ?>
        <p class="results-summary"><strong data-favorites-total><?= $total ?></strong> saved</p>
        <div class="recipe-grid">
            <?php foreach ($recipes as $recipe): ?>
                <?php View::partial('partials/recipe-card', ['recipe' => $recipe, 'next' => 'favorites.php']); ?>
            <?php endforeach; ?>
        </div>
        <?php View::partial('partials/pagination', ['page' => $page, 'pages' => $pages]); ?>
    <?php endif; ?>

    <div class="empty-state" data-favorites-empty <?= $recipes ? 'hidden' : '' ?>>
        <img src="assets/img/palayok.svg" alt="" width="72" height="72">
        <h2>No favorites yet</h2>
        <p>Tap the heart on any recipe to keep it here.</p>
        <a class="btn btn-primary" href="index.php">Browse recipes</a>
    </div>
</section>
