<?php
namespace Core;

/**
 * Session & Authentication Security Manager
 * Mengimplementasikan session_regenerate_id(true), CSRF, dan flash messages
 */
class Session
{
    private static bool $started = false;

    /**
     * Start secure session if not started
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        // Set secure session cookie parameters
        $lifetime = 60 * 60 * 24; // 24 hours
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
        self::$started = true;

        // Initialize CSRF token if not set
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Regenerate Session ID (Wajib untuk keamanan otentikasi)
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * Set Session Value
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get Session Value
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if key exists
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Flash Message - Set message for next request
     */
    public static function setFlash(string $key, string $message): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $message;
    }

    /**
     * Flash Message - Get and clear message
     */
    public static function getFlash(string $key): ?string
    {
        self::start();
        if (isset($_SESSION['_flash'][$key])) {
            $msg = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $msg;
        }
        return null;
    }

    /**
     * Flash Message - Check if exists
     */
    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Get CSRF Token
     */
    public static function getCsrfToken(): string
    {
        self::start();
        return $_SESSION['_csrf_token'] ?? '';
    }

    /**
     * Verify CSRF Token
     */
    public static function verifyCsrfToken(?string $token): bool
    {
        self::start();
        if (!$token || !isset($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    /**
     * Set Logged in User
     */
    public static function setUser(array $user): void
    {
        self::start();
        self::regenerate(); // Regenerate session ID upon login
        $_SESSION['user'] = $user;
    }

    /**
     * Get Current Logged in User
     */
    public static function getUser(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if User is Logged In
     */
    public static function isLoggedIn(): bool
    {
        self::start();
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    /**
     * Destroy Session (Logout)
     */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            // BUG-19 FIX: Gunakan time() - 3600 bukan magic number 42000
            setcookie(session_name(), '', time() - 3600,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        self::$started = false;
    }
}

