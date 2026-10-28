<?php
/**
 * @var int $page
 * @var int $pages
 */
if ($pages <= 1) {
    return;
}
?>
<nav class="pagination" aria-label="Pages">
    <?php if ($page > 1): ?>
        <a class="btn btn-ghost btn-sm" href="<?= e(query_url(['page' => $page - 1])) ?>" rel="prev">← Newer</a>
    <?php endif; ?>
    <span class="pagination-status">Page <?= $page ?> of <?= $pages ?></span>
    <?php if ($page < $pages): ?>
        <a class="btn btn-ghost btn-sm" href="<?= e(query_url(['page' => $page + 1])) ?>" rel="next">Older →</a>
    <?php endif; ?>
</nav>
