/* =========================================================================
   Lutong Bahay: front-end behavior (plain ES6, no libraries)

   Everything here is an enhancement: every form still submits and is
   validated by PHP if this file fails to load.
   ========================================================================= */

// Loaded in <head> without defer so this runs before the page paints,
// letting CSS hide JS-only widgets (and the folded mobile menu) without a flash.
document.documentElement.classList.replace('no-js', 'js');

document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initFlashes();
    initConfirmForms();
    initCounters();
    initValidation();
    initIngredientRows();
    initFavorites();
    initScaler();
});

/* ---- Small helpers ------------------------------------------------------- */

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

let toastTimer;
function toast(message, isError = false) {
    const box = $('.toast');
    if (!box) return;
    box.textContent = message;
    box.classList.toggle('is-error', isError);
    box.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { box.hidden = true; }, 2600);
}

/* ---- Header menu (phones) ------------------------------------------------ */

function initNav() {
    const toggle = $('.nav-toggle');
    const nav = $('#site-nav');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') !== 'true';
        toggle.setAttribute('aria-expanded', String(open));
        nav.classList.toggle('is-open', open);
    });
}

function initFlashes() {
    $$('.flash-close').forEach((button) => {
        button.addEventListener('click', () => button.closest('.flash').remove());
    });
}

/* ---- "Are you sure?" before deleting -------------------------------------- */

function initConfirmForms() {
    $$('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
}

/* ---- Character counters on long text fields ------------------------------ */

function initCounters() {
    $$('textarea[data-counter][maxlength]').forEach((field) => {
        const counter = document.createElement('span');
        counter.className = 'counter';
        counter.setAttribute('aria-hidden', 'true');
        field.insertAdjacentElement('afterend', counter);

        const update = () => {
            const max = field.maxLength;
            counter.textContent = `${field.value.length} / ${max}`;
            counter.classList.toggle('is-near', field.value.length > max * 0.9);
        };
        field.addEventListener('input', update);
        update();
    });
}

/* =========================================================================
   Client-side validation
   Uses the same limits the PHP Validator enforces (they're printed into the
   HTML attributes from App\Validation\Rules), but shows friendlier messages
   in the same spots the server's messages appear.
   ========================================================================= */

function initValidation() {
    $$('form[data-validate]').forEach((form) => {
        form.noValidate = true; // we show our own messages instead of the browser bubbles

        form.addEventListener('submit', (event) => {
            const fields = $$('input, select, textarea', form).filter(isCheckable);
            const fieldsOk = fields.map(validateField).every(Boolean);
            const ingredientsOk = form.matches('[data-recipe-form]') ? validateIngredients(form) : true;

            if (!fieldsOk || !ingredientsOk) {
                event.preventDefault();
                const firstBad = $('[aria-invalid="true"]', form);
                if (firstBad) firstBad.focus();
                return;
            }

            // Stop double submits (and duplicate recipes) on slow connections.
            const submit = $('button[type="submit"]', form);
            if (submit) {
                setTimeout(() => { submit.disabled = true; }, 0);
            }
        });

        // After the first complaint, re-check as the member types.
        form.addEventListener('input', (event) => {
            const field = event.target;
            if (field.getAttribute('aria-invalid') === 'true' && isCheckable(field)) {
                validateField(field);
            }
            if (field.dataset.match === undefined) {
                const twin = $(`[data-match="${field.name}"]`, form);
                if (twin && twin.value !== '') validateField(twin);
            }
        });

        form.addEventListener('focusout', (event) => {
            const field = event.target;
            if (isCheckable(field) && field.value !== '') validateField(field);
        });
    });
}

function isCheckable(field) {
    return field.matches('input:not([type="hidden"]), select, textarea')
        && !field.closest('template')
        && !field.name.startsWith('ingredient_'); // ingredient rows are checked together below
}

function validateField(field) {
    const message = messageFor(field);
    showError(field, message);
    return message === '';
}

function messageFor(field) {
    const value = field.value.trim();
    const label = labelFor(field);

    if (field.required && value === '') {
        return field.tagName === 'SELECT' ? `Choose a ${label.toLowerCase()}.` : `${label} is required.`;
    }
    if (value === '') return '';

    // Length is checked on the trimmed value, the same way PHP does it.
    if (field.minLength > 0 && value.length < field.minLength) {
        return `${label} must be at least ${field.minLength} characters (now ${value.length}).`;
    }
    if (field.maxLength > 0 && value.length > field.maxLength) {
        return `${label} must be at most ${field.maxLength} characters.`;
    }
    if (field.dataset.match) {
        const other = field.form.elements[field.dataset.match];
        if (other && other.value !== field.value) return field.dataset.matchMessage || 'Values do not match.';
    }

    const validity = field.validity;
    if (validity.typeMismatch && field.type === 'email') return 'Enter a valid email address.';
    if (validity.patternMismatch) return field.dataset.patternMessage || `${label} is not in the right format.`;
    if (validity.rangeUnderflow || validity.rangeOverflow || validity.stepMismatch || validity.badInput) {
        return `${label} must be a whole number from ${field.min} to ${field.max}.`;
    }
    if (!validity.valid) return field.validationMessage;
    return '';
}

function labelFor(field) {
    if (field.dataset.label) return field.dataset.label;
    const label = field.id ? $(`label[for="${field.id}"]`) : null;
    return label ? label.textContent.trim() : 'This field';
}

/** Puts the message in the field's own .field-error element (the one PHP also fills). */
function showError(field, message) {
    const errorBox = errorBoxFor(field);
    if (message) {
        field.setAttribute('aria-invalid', 'true');
    } else {
        field.removeAttribute('aria-invalid');
    }
    if (errorBox) errorBox.textContent = message;
}

function errorBoxFor(field) {
    const ids = (field.getAttribute('aria-describedby') || '').split(/\s+/);
    const id = ids.find((candidate) => candidate.endsWith('-error'));
    if (id && document.getElementById(id)) return document.getElementById(id);
    const wrapper = field.closest('.field');
    return wrapper ? $('.field-error', wrapper) : null;
}

/* ---- Recipe form: ingredient rows ------------------------------------------ */

const QUANTITY_PATTERN = /^(?:\d{1,4}(?:\.\d{1,3})?|\d{1,3}\/\d{1,3}|\d{1,4} \d{1,3}\/\d{1,3})$/;

function validateIngredients(form) {
    const rows = $$('[data-ingredient-row]', $('[data-ingredient-list]', form));
    let filled = 0;
    let ok = true;

    rows.forEach((row) => {
        const [qty, unit, name] = $$('input', row);
        const q = qty.value.trim().replace(/\s+/g, ' ');
        const u = unit.value.trim();
        const n = name.value.trim();
        const problems = [];

        [qty, unit, name].forEach((input) => input.removeAttribute('aria-invalid'));

        if (q === '' && u === '' && n === '') {
            $('.ingredient-error', row).textContent = '';
            return; // blank rows are ignored, same as on the server
        }
        filled += 1;

        if (n === '') {
            problems.push('name the ingredient');
            name.setAttribute('aria-invalid', 'true');
        }
        if (q !== '') {
            const bad = !QUANTITY_PATTERN.test(q) || /\/0+(?:\s|$)/.test(q) || parseQuantity(q) <= 0;
            if (bad) {
                problems.push('use an amount like 2, 1.5, 1/2 or 1 1/2');
                qty.setAttribute('aria-invalid', 'true');
            }
        } else if (u !== '') {
            problems.push('add an amount for the unit');
            qty.setAttribute('aria-invalid', 'true');
        }

        const message = problems.length ? capitalize(problems.join(', ')) + '.' : '';
        $('.ingredient-error', row).textContent = message;
        if (message) ok = false;
    });

    const listError = $('[data-ingredients-error]', form);
    if (filled === 0) {
        listError.textContent = 'Add at least one ingredient.';
        const firstName = $('[data-ingredient-name]', form);
        if (firstName) firstName.setAttribute('aria-invalid', 'true');
        return false;
    }
    listError.textContent = '';
    return ok;
}

function initIngredientRows() {
    const form = $('form[data-recipe-form]');
    if (!form) return;

    const list = $('[data-ingredient-list]', form);
    const template = $('template[data-ingredient-template]', form);
    const addButton = $('[data-add-ingredient]', form);
    const max = Number(list.dataset.max) || 40;

    const refresh = () => {
        const count = list.children.length;
        addButton.disabled = count >= max;
        addButton.textContent = count >= max ? `Maximum of ${max} ingredients` : '+ Add ingredient';
    };

    const addRow = () => {
        if (list.children.length >= max) return;
        const row = template.content.firstElementChild.cloneNode(true);
        row.classList.add('is-new');
        list.appendChild(row);
        $('input', row).focus();
        refresh();
    };

    addButton.addEventListener('click', addRow);

    list.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-remove-ingredient]');
        if (!remove) return;
        const row = remove.closest('[data-ingredient-row]');
        if (list.children.length === 1) {
            // Keep one row on screen; just empty it.
            $$('input', row).forEach((input) => { input.value = ''; });
            $('.ingredient-error', row).textContent = '';
        } else {
            row.remove();
        }
        refresh();
    });

    // Enter in the last row adds a new row instead of submitting the whole form.
    list.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || event.target.tagName !== 'INPUT') return;
        event.preventDefault();
        const row = event.target.closest('[data-ingredient-row]');
        if (row === list.lastElementChild) {
            addRow();
        } else {
            $('input', row.nextElementSibling).focus();
        }
    });

    // Re-check a row as soon as it's edited, once it has shown an error.
    list.addEventListener('input', (event) => {
        const row = event.target.closest('[data-ingredient-row]');
        if (row && $('.ingredient-error', row).textContent !== '') validateIngredients(form);
    });

    refresh();
}

/* ---- Favorites without reloading the page ------------------------------------ */

function initFavorites() {
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-favorite-form]');
        if (!form) return;
        event.preventDefault();

        const button = $('button', form);
        if (button.getAttribute('aria-busy') === 'true') return;
        button.setAttribute('aria-busy', 'true');

        try {
            // getAttribute, not form.action: a field named "action" would shadow that property.
            const response = await fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const data = await response.json().catch(() => ({}));

            if (response.status === 401) {
                window.location.href = 'login.php';
                return;
            }
            if (!response.ok || typeof data.favorited !== 'boolean') {
                throw new Error(data.error || 'Could not update favorites. Please try again.');
            }

            const recipeId = form.elements.recipe_id.value;
            $$(`form[data-favorite-form]`).forEach((other) => {
                if (other.elements.recipe_id.value === recipeId) renderFavorite(other, data);
            });
            toast(data.favorited ? 'Saved to your favorites ♥' : 'Removed from your favorites');

            const favoritesPage = $('[data-favorites-page]');
            if (favoritesPage && !data.favorited) removeFavoriteCard(form, favoritesPage);
        } catch (error) {
            toast(error.message, true);
        } finally {
            button.removeAttribute('aria-busy');
        }
    });
}

function renderFavorite(form, data) {
    const button = $('button', form);
    form.elements.mode.value = data.favorited ? 'remove' : 'add';
    button.setAttribute('aria-pressed', String(data.favorited));
    button.title = data.favorited ? 'Remove from favorites' : 'Save to favorites';
    $('.favorite-count', button).textContent = data.count;

    const label = $('.favorite-label', button);
    if (!button.classList.contains('favorite-btn-compact')) {
        label.textContent = data.favorited ? 'Saved' : 'Save';
    }

    button.classList.add('is-popping');
    setTimeout(() => button.classList.remove('is-popping'), 200);
}

function removeFavoriteCard(form, page) {
    const card = form.closest('[data-recipe-card]');
    if (!card) return;
    card.classList.add('is-leaving');
    setTimeout(() => {
        card.remove();
        const total = $('[data-favorites-total]', page);
        const left = $$('[data-recipe-card]', page).length;
        if (total) total.textContent = Math.max(0, Number(total.textContent) - 1);
        if (left === 0) {
            $$('.recipe-grid, .results-summary, .pagination', page).forEach((el) => el.remove());
            $('[data-favorites-empty]', page).hidden = false;
        }
    }, 250);
}

/* =========================================================================
   Servings scaler (our extra feature)
   Each amount is stored for the recipe's original servings; we multiply
   by (wanted / original) and show friendly kitchen fractions.
   ========================================================================= */

function initScaler() {
    const scaler = $('[data-scaler]');
    if (!scaler) return;

    const base = Number(scaler.dataset.baseServings);
    const input = $('input', scaler);
    const reset = $('[data-scaler-reset]', scaler);
    const min = Number(input.min);
    const max = Number(input.max);
    const amounts = $$('.qty[data-qty]');

    const apply = (servings) => {
        const wanted = Math.min(max, Math.max(min, Math.round(servings) || base));
        input.value = wanted;
        const factor = wanted / base;

        amounts.forEach((amount) => {
            amount.textContent = formatQuantity(Number(amount.dataset.qty) * factor);
            amount.classList.toggle('is-scaled', factor !== 1);
        });
        reset.hidden = wanted === base;
    };

    $$('[data-step]', scaler).forEach((button) => {
        button.addEventListener('click', () => apply(Number(input.value) + Number(button.dataset.step)));
    });
    input.addEventListener('change', () => apply(Number(input.value)));
    reset.addEventListener('click', () => apply(base));
}

const FRACTIONS = [
    [1 / 8, '⅛'], [1 / 4, '¼'], [1 / 3, '⅓'], [3 / 8, '⅜'], [1 / 2, '½'],
    [5 / 8, '⅝'], [2 / 3, '⅔'], [3 / 4, '¾'], [7 / 8, '⅞'],
];

/** Same rules as App\Support\Quantity::format() in PHP. */
function formatQuantity(value) {
    const whole = Math.floor(value);
    const rest = value - whole;

    if (rest < 0.02) return String(whole);
    if (rest > 0.98) return String(whole + 1);

    const match = FRACTIONS.find(([amount]) => Math.abs(rest - amount) < 0.02);
    if (match) return (whole > 0 ? String(whole) : '') + match[1];

    return String(Number(value.toFixed(2)));
}

function parseQuantity(text) {
    return text.split(' ').reduce((sum, part) => {
        if (part.includes('/')) {
            const [top, bottom] = part.split('/').map(Number);
            return sum + top / bottom;
        }
        return sum + Number(part);
    }, 0);
}

function capitalize(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
}
