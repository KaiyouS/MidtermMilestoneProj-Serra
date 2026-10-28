<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Owns everything stored in $_SESSION: the logged-in member, flash messages,
 * old form input, and the CSRF token. No other class touches $_SESSION.
 */
final class SessionManager
{
    private const USER_KEY = 'user';
    private const FLASH_KEY = '_flash';
    private const OLD_KEY = '_old';
    private const CSRF_KEY = '_csrf';

    /** Flash messages and old input carried over from the previous request. */
    private array $flashes = [];
    private array $old = [];

    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        session_name('lutongbahay_sid');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => ($_SERVER['HTTPS'] ?? '') === 'on',
        ]);
        session_start();

        // Flash data lives for exactly one request: read it now, then forget it.
        $this->flashes = $_SESSION[self::FLASH_KEY] ?? [];
        $this->old = $_SESSION[self::OLD_KEY] ?? [];
        unset($_SESSION[self::FLASH_KEY], $_SESSION[self::OLD_KEY]);
    }

    // ---- Authentication ---------------------------------------------------

    public function login(array $user): void
    {
        // New session ID on login prevents session fixation.
        session_regenerate_id(true);
        $_SESSION[self::USER_KEY] = [
            'id'   => (int) $user['id'],
            'name' => $user['display_name'],
        ];
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'],
            ]);
        }

        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION[self::USER_KEY]['id']);
    }

    public function userId(): ?int
    {
        return $_SESSION[self::USER_KEY]['id'] ?? null;
    }

    public function userName(): ?string
    {
        return $_SESSION[self::USER_KEY]['name'] ?? null;
    }

    /** Gate for member-only pages. Returns the member's ID so pages can use it directly. */
    public function requireLogin(): int
    {
        if (!$this->isLoggedIn()) {
            $this->flash('info', 'Please log in or create an account first.');
            $target = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
            $query = $_SERVER['QUERY_STRING'] ?? '';
            redirect('login.php?next=' . rawurlencode($target . ($query !== '' ? '?' . $query : '')));
        }

        return $this->userId();
    }

    /** Login and register pages make no sense for someone already logged in. */
    public function requireGuest(): void
    {
        if ($this->isLoggedIn()) {
            redirect('index.php');
        }
    }

    // ---- Flash messages & old input ----------------------------------------

    public function flash(string $type, string $message): void
    {
        $_SESSION[self::FLASH_KEY][] = ['type' => $type, 'message' => $message];
    }

    /** @return list<array{type: string, message: string}> */
    public function flashes(): array
    {
        $flashes = $this->flashes;
        $this->flashes = [];
        return $flashes;
    }

    /** Keep submitted values for the next request (used when we redirect after an error). */
    public function flashOld(array $input): void
    {
        $_SESSION[self::OLD_KEY] = $input;
    }

    public function old(string $key, string $default = ''): string
    {
        $value = $this->old[$key] ?? $default;
        return is_string($value) ? $value : $default;
    }

    // ---- CSRF protection ---------------------------------------------------

    public function csrfToken(): string
    {
        if (empty($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::CSRF_KEY];
    }

    public function isValidCsrf(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION[self::CSRF_KEY])
            && hash_equals($_SESSION[self::CSRF_KEY], $token);
    }

    /** Every form that changes data must call this before doing anything. */
    public function requireCsrf(): void
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$this->isValidCsrf($token)) {
            abort(419, 'Your session expired or the form was submitted twice. Please go back, refresh, and try again.');
        }
    }
}
