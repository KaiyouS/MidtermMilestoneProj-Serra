<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Renders templates from views/. A page passes plain arrays in; templates only
 * echo (always through e()) and never run SQL.
 */
final class View
{
    private static string $path;
    private static SessionManager $session;

    public static function setDefaults(string $path, SessionManager $session): void
    {
        self::$path = rtrim($path, '/');
        self::$session = $session;
    }

    /** Render a page template inside views/layout.php and send it. */
    public static function render(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        $data['session'] = self::$session;
        $content = self::capture($template, $data);
        echo self::capture('layout', $data + ['content' => $content]);
    }

    /** Render a small reusable piece (e.g. a recipe card) from inside a template. */
    public static function partial(string $template, array $data = []): void
    {
        $data['session'] = self::$session;
        echo self::capture($template, $data);
    }

    private static function capture(string $template, array $data): string
    {
        $file = self::$path . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
