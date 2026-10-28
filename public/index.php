<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Core\View;
use App\Repositories\CategoryRepository;
use App\Repositories\RecipeRepository;
use App\Validation\Rules;

$userId = $session->requireLogin();

const PER_PAGE = 9;

$q = is_string($_GET['q'] ?? null) ? trim(mb_substr($_GET['q'], 0, Rules::SEARCH_MAX)) : '';
$categoryId = int_param($_GET, 'category');
$mine = ($_GET['mine'] ?? '') === '1';
$page = int_param($_GET, 'page') ?? 1;

$filters = [
    'q'        => $q,
    'category' => $categoryId,
    'author'   => $mine ? $userId : null,
];

$recipes = new RecipeRepository($pdo);
$total = $recipes->count($filters);
$pages = max(1, (int) ceil($total / PER_PAGE));
$page = min($page, $pages);

View::render('recipes/index', [
    'title'      => $mine ? 'My recipes' : 'Recipes',
    'activeNav'  => $mine ? 'mine' : 'browse',
    'recipes'    => $recipes->search($filters, $userId, PER_PAGE, ($page - 1) * PER_PAGE),
    'categories' => (new CategoryRepository($pdo))->allWithCounts(),
    'q'          => $q,
    'categoryId' => $categoryId,
    'mine'       => $mine,
    'total'      => $total,
    'page'       => $page,
    'pages'      => $pages,
]);
