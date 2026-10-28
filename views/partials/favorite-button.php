<?php
/**
 * Save / unsave toggle. It's a real form so it still works without JavaScript;
 * app.js intercepts the submit and uses fetch() so the page doesn't reload.
 *
 * @var array $recipe
 * @var string $next page to come back to when JavaScript is off
 * @var bool $compact smaller version for cards
 */
$saved = (bool) $recipe['is_favorite'];
$compact ??= false;
?>
<form class="favorite-form" method="post" action="favorite.php" data-favorite-form>
    <input type="hidden" name="_token" value="<?= e($session->csrfToken()) ?>">
    <input type="hidden" name="recipe_id" value="<?= e($recipe['id']) ?>">
    <input type="hidden" name="mode" value="<?= $saved ? 'remove' : 'add' ?>">
    <input type="hidden" name="next" value="<?= e($next) ?>">
    <button type="submit" class="favorite-btn<?= $compact ? ' favorite-btn-compact' : '' ?>"
            aria-pressed="<?= $saved ? 'true' : 'false' ?>"
            title="<?= $saved ? 'Remove from favorites' : 'Save to favorites' ?>">
        <svg class="heart" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 3 4.5 6.7 4.5c2.1 0 3.6 1.2 4.3 2.4h2c.7-1.2 2.2-2.4 4.3-2.4 3.7 0 5.8 3.9 4.3 7.3C19.5 16.4 12 21 12 21z"/>
        </svg>
        <span class="favorite-label"><?= $compact ? '' : ($saved ? 'Saved' : 'Save') ?></span>
        <span class="favorite-count" aria-label="saved by"><?= e($recipe['favorite_count']) ?></span>
    </button>
</form>
