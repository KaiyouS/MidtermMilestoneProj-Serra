<?php

use App\Core\View;
use App\Support\Quantity;
use App\Validation\Rules;

/**
 * @var array $recipe
 * @var list<array> $ingredients
 * @var list<string> $steps
 * @var list<array> $comments
 */
$isOwner = (int) $recipe['user_id'] === $session->userId();
$here = 'recipe.php?id=' . $recipe['id'];
?>
<article class="container recipe-page">
    <a class="back-link" href="index.php">← All recipes</a>

    <header class="recipe-header">
        <span class="tag tag-cat-<?= e($recipe['category_id']) ?>">
            <a href="index.php?category=<?= e($recipe['category_id']) ?>"><?= e($recipe['category']) ?></a>
        </span>
        <h1><?= e($recipe['title']) ?></h1>
        <p class="recipe-meta">
            Shared by <strong><?= e($recipe['author']) ?></strong>
            on <time datetime="<?= e($recipe['created_at']) ?>"><?= e(format_datetime($recipe['created_at'])) ?></time>
        </p>
        <?php if ($recipe['updated_at']): ?>
            <p class="edited-note">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM14 6l4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                <span>Edited by the author on <time datetime="<?= e($recipe['updated_at']) ?>"><?= e(format_datetime($recipe['updated_at'])) ?></time></span>
            </p>
        <?php endif; ?>
        <p class="recipe-lead"><?= nl2br(e($recipe['description'])) ?></p>

        <div class="recipe-actions">
            <?php View::partial('partials/favorite-button', ['recipe' => $recipe, 'next' => $here]); ?>
            <?php if ($isOwner): ?>
                <a class="btn btn-ghost" href="recipe-edit.php?id=<?= e($recipe['id']) ?>">Edit recipe</a>
                <form method="post" action="recipe-delete.php"
                      data-confirm="Delete “<?= e($recipe['title']) ?>”? Its comments and favorites will be removed too. This cannot be undone.">
                    <input type="hidden" name="_token" value="<?= e($session->csrfToken()) ?>">
                    <input type="hidden" name="id" value="<?= e($recipe['id']) ?>">
                    <button class="btn btn-danger-ghost" type="submit">Delete</button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <div class="recipe-body">
        <section class="card ingredients-card" aria-labelledby="ingredients-heading">
            <div class="ingredients-head">
                <h2 id="ingredients-heading">Ingredients</h2>
                <p class="servings-static">Serves <?= e($recipe['servings']) ?></p>
            </div>

            <!-- Servings scaler (our extra feature). Hidden until app.js is running. -->
            <div class="scaler js-only" data-scaler data-base-servings="<?= e($recipe['servings']) ?>">
                <span class="scaler-label" id="scaler-label">Cooking for</span>
                <div class="stepper" role="group" aria-labelledby="scaler-label">
                    <button type="button" class="stepper-btn" data-step="-1" aria-label="Fewer servings">−</button>
                    <input type="number" id="servings" inputmode="numeric"
                           min="<?= Rules::SERVINGS_MIN ?>" max="<?= Rules::SERVINGS_MAX ?>"
                           value="<?= e($recipe['servings']) ?>" aria-label="Servings">
                    <button type="button" class="stepper-btn" data-step="1" aria-label="More servings">+</button>
                </div>
                <span class="scaler-unit">people</span>
                <button type="button" class="link-button scaler-reset" data-scaler-reset hidden>Reset</button>
            </div>

            <ul class="ingredient-list">
                <?php foreach ($ingredients as $ingredient): ?>
                    <li>
                        <span class="ingredient-amount">
                            <?php if ($ingredient['quantity'] !== null): ?>
                                <span class="qty" data-qty="<?= e((float) $ingredient['quantity']) ?>"><?= e(Quantity::format((float) $ingredient['quantity'])) ?></span>
                            <?php endif; ?>
                            <?= e($ingredient['unit']) ?>
                        </span>
                        <span class="ingredient-item"><?= e($ingredient['name']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="steps-section" aria-labelledby="steps-heading">
            <h2 id="steps-heading">How to cook it</h2>
            <ol class="step-list">
                <?php foreach ($steps as $step): ?>
                    <li><?= e($step) ?></li>
                <?php endforeach; ?>
            </ol>
        </section>
    </div>

    <section id="comments" class="comments" aria-labelledby="comments-heading">
        <h2 id="comments-heading">
            Kwentuhan
            <span class="muted">(<?= count($comments) ?> <?= count($comments) === 1 ? 'comment' : 'comments' ?>)</span>
        </h2>

        <?php if (!$comments): ?>
            <p class="muted">No comments yet. Tried it? Tell the cook how it went.</p>
        <?php endif; ?>

        <ol class="comment-list">
            <?php foreach ($comments as $comment): ?>
                <?php $ownComment = (int) $comment['user_id'] === $session->userId(); ?>
                <li class="comment<?= $ownComment ? ' comment-own' : '' ?>" id="comment-<?= e($comment['id']) ?>">
                    <div class="comment-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($comment['author'], 0, 1))) ?></div>
                    <div class="comment-main">
                        <p class="comment-meta">
                            <strong><?= e($comment['author']) ?></strong>
                            <?php if ((int) $comment['user_id'] === (int) $recipe['user_id']): ?>
                                <span class="tag tag-cook">Cook</span>
                            <?php endif; ?>
                            <time datetime="<?= e($comment['created_at']) ?>"><?= e(format_datetime($comment['created_at'])) ?></time>
                            <?php if ($comment['updated_at']): ?>
                                <span class="edited" title="Edited <?= e(format_datetime($comment['updated_at'])) ?>">· edited</span>
                            <?php endif; ?>
                        </p>
                        <p class="comment-body"><?= nl2br(e($comment['body'])) ?></p>

                        <?php if ($ownComment): ?>
                            <div class="comment-tools">
                                <details class="comment-edit">
                                    <summary>Edit</summary>
                                    <form method="post" action="comment-update.php" data-validate>
                                        <input type="hidden" name="_token" value="<?= e($session->csrfToken()) ?>">
                                        <input type="hidden" name="id" value="<?= e($comment['id']) ?>">
                                        <div class="field">
                                            <label class="visually-hidden" for="edit-body-<?= e($comment['id']) ?>">Edit your comment</label>
                                            <textarea id="edit-body-<?= e($comment['id']) ?>" name="body" rows="3" required data-label="Comment"
                                                      minlength="<?= Rules::COMMENT_MIN ?>" maxlength="<?= Rules::COMMENT_MAX ?>"
                                                      aria-describedby="edit-body-<?= e($comment['id']) ?>-error"><?= e($comment['body']) ?></textarea>
                                            <p class="field-error" id="edit-body-<?= e($comment['id']) ?>-error"></p>
                                        </div>
                                        <button class="btn btn-primary btn-sm" type="submit">Save changes</button>
                                    </form>
                                </details>
                                <form method="post" action="comment-delete.php" data-confirm="Delete your comment?">
                                    <input type="hidden" name="_token" value="<?= e($session->csrfToken()) ?>">
                                    <input type="hidden" name="id" value="<?= e($comment['id']) ?>">
                                    <button class="link-button link-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>

        <form class="card comment-form" method="post" action="comment-store.php" data-validate>
            <input type="hidden" name="_token" value="<?= e($session->csrfToken()) ?>">
            <input type="hidden" name="recipe_id" value="<?= e($recipe['id']) ?>">
            <div class="field">
                <label for="body">Add a comment</label>
                <textarea id="body" name="body" rows="3" required
                          minlength="<?= Rules::COMMENT_MIN ?>" maxlength="<?= Rules::COMMENT_MAX ?>"
                          placeholder="Tried it? Any tips or substitutions?"
                          data-counter data-label="Comment"
                          aria-describedby="body-error"><?= e($session->old('body')) ?></textarea>
                <p class="field-error" id="body-error"></p>
            </div>
            <button class="btn btn-primary" type="submit">Post comment</button>
        </form>
    </section>
</article>
