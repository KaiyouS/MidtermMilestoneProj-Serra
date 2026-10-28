<?php

use App\Support\Quantity;
use App\Validation\Rules;

/**
 * One ingredient row in the recipe form. Also rendered inside a <template>
 * so app.js can clone it when the member clicks "Add ingredient".
 *
 * @var array{quantity_text: string, unit: string, name: string} $row
 * @var string|null $error
 */
$error ??= null;
?>
<li class="ingredient-row" data-ingredient-row>
    <span class="ingredient-handle" aria-hidden="true"></span>
    <div class="ingredient-fields">
        <label class="ingredient-field ingredient-qty">
            <span class="mini-label">Amount</span>
            <input type="text" name="ingredient_quantity[]" inputmode="decimal"
                   value="<?= e($row['quantity_text']) ?>"
                   pattern="<?= e(Quantity::PATTERN) ?>" maxlength="12"
                   placeholder="1 1/2"
                   data-pattern-message="Use an amount like 2, 1.5, 1/2 or 1 1/2.">
        </label>
        <label class="ingredient-field ingredient-unit">
            <span class="mini-label">Unit</span>
            <input type="text" name="ingredient_unit[]" value="<?= e($row['unit']) ?>"
                   maxlength="<?= Rules::UNIT_MAX ?>" placeholder="cups" list="unit-suggestions">
        </label>
        <label class="ingredient-field ingredient-name">
            <span class="mini-label">Ingredient</span>
            <input type="text" name="ingredient_name[]" value="<?= e($row['name']) ?>"
                   maxlength="<?= Rules::INGREDIENT_NAME_MAX ?>" placeholder="chicken thighs, cut up"
                   data-ingredient-name>
        </label>
    </div>
    <button type="button" class="icon-btn ingredient-remove" data-remove-ingredient aria-label="Remove this ingredient">×</button>
    <p class="field-error ingredient-error"><?= e($error ?? '') ?></p>
</li>
