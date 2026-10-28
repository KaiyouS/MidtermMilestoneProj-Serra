<?php

use App\Core\View;
use App\Validation\Rules;

/**
 * @var list<array> $recipes
 * @var list<array> $categories
 * @var string $q
 * @var int|null $categoryId
 * @var bool $mine
 * @var int $total
 * @var int $page
 * @var int $pages
 */
$filtering = $q !== '' || $categoryId !== null;
$currentUrl = query_url([]);
$categoryName = null;
foreach ($categories as $category) {
    if ((int) $category['id'] === $categoryId) {
        $categoryName = $category['name'];
    }
}
?>
<section class="hero">
    <div class="container">
        <?php if ($mine): ?>
            <p class="eyebrow">Your kitchen</p>
            <h1>My recipes</h1>
            <p class="hero-lead">Everything you've shared. Open one to edit or remove it.</p>
        <?php else: ?>
            <p class="eyebrow">Ano'ng ulam natin?</p>
            <h1>Fresh from the barangay's kitchens</h1>
            <p class="hero-lead">Newest recipes first. Search by dish or ingredient, or pick a category.</p>
        <?php endif; ?>

        <form class="search-bar" method="get" action="index.php" role="search">
            <?php if ($mine): ?>
                <input type="hidden" name="mine" value="1">
            <?php endif; ?>
            <label class="visually-hidden" for="q">Search recipes</label>
            <input id="q" name="q" type="search" value="<?= e($q) ?>"
                   maxlength="<?= Rules::SEARCH_MAX ?>"
                   placeholder="Try “adobo”, “gata”, or “malunggay”">
            <label class="visually-hidden" for="category">Category</label>
            <select id="category" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['id']) ?>" <?= (int) $category['id'] === $categoryId ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>
</section>

<section class="container">
    <nav class="chips" aria-label="Categories">
        <a class="chip" href="<?= e(query_url(['category' => null, 'page' => null])) ?>"
            <?= $categoryId === null ? 'aria-current="true"' : '' ?>>All</a>
        <?php foreach ($categories as $category): ?>
            <a class="chip chip-cat-<?= e($category['id']) ?>"
               href="<?= e(query_url(['category' => $category['id'], 'page' => null])) ?>"
               title="<?= e($category['description']) ?>"
                <?= (int) $category['id'] === $categoryId ? 'aria-current="true"' : '' ?>>
                <?= e($category['name']) ?>
                <span class="chip-count"><?= e($category['recipe_count']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="results-bar">
        <p class="results-summary" aria-live="polite">
            <strong><?= $total ?></strong> <?= $total === 1 ? 'recipe' : 'recipes' ?>
            <?php if ($q !== ''): ?> matching “<?= e($q) ?>”<?php endif; ?>
            <?php if ($categoryName !== null): ?> in <?= e($categoryName) ?><?php endif; ?>
        </p>
        <?php if ($filtering): ?>
            <a class="link-quiet" href="<?= $mine ? 'index.php?mine=1' : 'index.php' ?>">Clear filters</a>
        <?php endif; ?>
    </div>

    <?php if ($recipes): ?>
        <div class="recipe-grid">
            <?php foreach ($recipes as $recipe): ?>
                <?php View::partial('partials/recipe-card', ['recipe' => $recipe, 'next' => $currentUrl]); ?>
            <?php endforeach; ?>
        </div>
        <?php View::partial('partials/pagination', ['page' => $page, 'pages' => $pages]); ?>
    <?php else: ?>
        <div class="empty-state">
            <img src="assets/img/palayok.svg" alt="" width="72" height="72">
            <?php if ($filtering): ?>
                <h2>Walang nahanap. Nothing matched.</h2>
                <p>Try a different word or another category.</p>
            <?php elseif ($mine): ?>
                <h2>You haven't shared anything yet</h2>
                <p>What's the dish everyone asks you to bring to the fiesta?</p>
                <a class="btn btn-primary" href="recipe-create.php">Share your first recipe</a>
            <?php else: ?>
                <h2>The kitchen is quiet</h2>
                <p>Be the first to share a recipe with the barangay.</p>
                <a class="btn btn-primary" href="recipe-create.php">Share a recipe</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
