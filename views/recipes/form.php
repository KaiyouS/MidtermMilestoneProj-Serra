<?php

use App\Core\View;
use App\Validation\Rules;

/**
 * Shared by recipe-create.php and recipe-edit.php.
 *
 * @var bool $editing
 * @var array $form title, category_id, description, servings, steps, ingredients[]
 * @var array<string, string> $errors
 * @var list<array> $categories
 */
$action = $editing ? 'recipe-edit.php?id=' . $form['id'] : 'recipe-create.php';
$rows = $form['ingredients'] ?: [['quantity_text' => '', 'unit' => '', 'name' => '']];
$blankRow = ['quantity_text' => '', 'unit' => '', 'name' => ''];

$invalid = static fn (string $field): string => isset($errors[$field]) ? 'aria-invalid="true"' : '';
?>
<section class="container narrow form-page">
    <a class="back-link" href="<?= $editing ? 'recipe.php?id=' . e($form['id']) : 'index.php' ?>">← <?= $editing ? 'Back to recipe' : 'All recipes' ?></a>
    <h1><?= $editing ? 'Edit recipe' : 'Share a recipe' ?></h1>
    <p class="muted"><?= $editing
        ? 'Other members will see that this recipe was edited and when.'
        : 'Write it the way you would explain it to a neighbor in your kitchen.' ?></p>

    <?php if ($errors): ?>
        <p class="form-alert" role="alert">Please fix the highlighted fields below.</p>
    <?php endif; ?>

    <form class="card recipe-form" method="post" action="<?= e($action) ?>" data-validate data-recipe-form>
        <input type="hidden" name="_token" value="<?= e($session->csrfToken()) ?>">

        <div class="field">
            <label for="title">Recipe title</label>
            <input id="title" name="title" type="text" required data-label="Title"
                   minlength="<?= Rules::TITLE_MIN ?>" maxlength="<?= Rules::TITLE_MAX ?>"
                   value="<?= e($form['title']) ?>" placeholder="e.g. Lola Ising's Chicken Adobo"
                   <?= $invalid('title') ?> aria-describedby="title-error">
            <p class="field-error" id="title-error"><?= e($errors['title'] ?? '') ?></p>
        </div>

        <div class="field-row">
            <div class="field">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required <?= $invalid('category_id') ?> aria-describedby="category_id-error">
                    <option value="">Choose one…</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category['id']) ?>" <?= (int) $category['id'] === (int) $form['category_id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?> (<?= e($category['description']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="field-error" id="category_id-error"><?= e($errors['category_id'] ?? '') ?></p>
            </div>

            <div class="field field-small">
                <label for="servings">Servings</label>
                <input id="servings" name="servings" type="number" inputmode="numeric" required
                       min="<?= Rules::SERVINGS_MIN ?>" max="<?= Rules::SERVINGS_MAX ?>" step="1"
                       value="<?= e($form['servings']) ?>"
                       <?= $invalid('servings') ?> aria-describedby="servings-hint servings-error">
                <p class="field-hint" id="servings-hint">Used by the servings scaler.</p>
                <p class="field-error" id="servings-error"><?= e($errors['servings'] ?? '') ?></p>
            </div>
        </div>

        <div class="field">
            <label for="description">Short description</label>
            <textarea id="description" name="description" rows="3" required data-label="Description"
                      minlength="<?= Rules::DESCRIPTION_MIN ?>" maxlength="<?= Rules::DESCRIPTION_MAX ?>"
                      placeholder="What makes this dish special? When do you usually cook it?"
                      data-counter
                      <?= $invalid('description') ?> aria-describedby="description-error"><?= e($form['description']) ?></textarea>
            <p class="field-error" id="description-error"><?= e($errors['description'] ?? '') ?></p>
        </div>

        <fieldset class="field ingredients-fieldset">
            <legend>Ingredients</legend>
            <p class="field-hint">One ingredient per row. Amount and unit are optional (leave them blank for “salt, to taste”). Amounts like <code>1/2</code> or <code>1 1/2</code> work.</p>

            <ol class="ingredient-rows" data-ingredient-list data-max="<?= Rules::INGREDIENTS_MAX ?>">
                <?php foreach ($rows as $i => $row): ?>
                    <?php View::partial('partials/ingredient-row', ['row' => $row, 'error' => $errors["ingredients.{$i}"] ?? null]); ?>
                <?php endforeach; ?>
            </ol>

            <template data-ingredient-template>
                <?php View::partial('partials/ingredient-row', ['row' => $blankRow]); ?>
            </template>

            <datalist id="unit-suggestions">
                <?php foreach (['cup', 'cups', 'tbsp', 'tsp', 'g', 'kg', 'ml', 'L', 'pc', 'pcs', 'cloves', 'bunch', 'can', 'pack', 'thumb', 'pinch'] as $unit): ?>
                    <option value="<?= e($unit) ?>">
                <?php endforeach; ?>
            </datalist>

            <p class="field-error" id="ingredients-error" data-ingredients-error><?= e($errors['ingredients'] ?? '') ?></p>
            <button type="button" class="btn btn-ghost btn-sm" data-add-ingredient>+ Add ingredient</button>
        </fieldset>

        <div class="field">
            <label for="steps">Cooking steps</label>
            <p class="field-hint" id="steps-hint">One step per line. We'll number them for you.</p>
            <textarea id="steps" name="steps" rows="9" required data-label="Steps"
                      minlength="<?= Rules::STEPS_MIN_CHARS ?>" maxlength="<?= Rules::STEPS_MAX_CHARS ?>"
                      placeholder="Marinate the chicken in soy sauce, vinegar and garlic for 30 minutes.&#10;Brown the chicken in a little oil.&#10;Pour in the marinade, add bay leaves and peppercorns, then simmer for 30 minutes."
                      data-counter
                      <?= $invalid('steps') ?> aria-describedby="steps-hint steps-error"><?= e($form['steps']) ?></textarea>
            <p class="field-error" id="steps-error"><?= e($errors['steps'] ?? '') ?></p>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $editing ? 'Save changes' : 'Publish recipe' ?></button>
            <a class="btn btn-ghost" href="<?= $editing ? 'recipe.php?id=' . e($form['id']) : 'index.php' ?>">Cancel</a>
        </div>
    </form>
</section>
