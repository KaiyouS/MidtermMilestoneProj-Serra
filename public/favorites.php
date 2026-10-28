<?php

declare(strict_types=1);

// bootstrap.php loads the classes, starts the session, and includes config/database.php (the one DB connection file).
require __DIR__ . '/../bootstrap.php';

use App\Core\View;
use App\Repositories\RecipeRepository;

$userId = $session->requireLogin();

const PER_PAGE = 12;

$filters = ['favorites_of' => $userId];
$recipes = new RecipeRepository($pdo);
$total = $recipes->count($filters);
$pages = max(1, (int) ceil($total / PER_PAGE));
$page = min(int_param($_GET, 'page') ?? 1, $pages);

View::render('favorites/index', [
    'title'     => 'My favorites',
    'activeNav' => 'favorites',
    'recipes'   => $recipes->search($filters, $userId, PER_PAGE, ($page - 1) * PER_PAGE),
    'total'     => $total,
    'page'      => $page,
    'pages'     => $pages,
]);
