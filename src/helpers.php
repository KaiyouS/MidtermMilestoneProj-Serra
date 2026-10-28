<?php

declare(strict_types=1);

use App\Core\View;

/** Escape anything a user typed before it goes into HTML. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function abort(int $status, string $message = ''): never
{
    $titles = [
        403 => 'Not allowed',
        404 => 'Not found',
        405 => 'Method not allowed',
        419 => 'Page expired',
    ];

    View::render('errors/error', [
        'title'   => $titles[$status] ?? 'Something went wrong',
        'status'  => $status,
        'message' => $message !== '' ? $message : 'We could not find what you were looking for.',
    ], $status);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Read a positive integer ID from $_GET / $_POST, or null if missing or invalid. */
function int_param(array $source, string $key): ?int
{
    $value = filter_var($source[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $value === false ? null : $value;
}

function format_date(string $datetime): string
{
    return date('M j, Y', strtotime($datetime));
}

function format_datetime(string $datetime): string
{
    return date('M j, Y · g:i A', strtotime($datetime));
}

/** Build the current page's URL with some query parameters changed (used for filters and pagination). */
function query_url(array $changes): string
{
    $params = array_filter(
        array_merge($_GET, $changes),
        static fn ($v) => $v !== null && $v !== ''
    );
    $page = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    return $page . ($params ? '?' . http_build_query($params) : '');
}

/**
 * Only allow redirecting back to one of our own pages (e.g. "recipe.php?id=3"),
 * never to another site.
 */
function safe_next(?string $next, string $fallback = 'index.php'): string
{
    if (is_string($next) && preg_match('/^[a-z0-9-]+\.php(\?[^\s#]*)?$/i', $next)) {
        return $next;
    }
    return $fallback;
}
