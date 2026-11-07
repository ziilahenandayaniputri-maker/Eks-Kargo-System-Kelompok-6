<?php
namespace Core;

/**
 * Security & Anti-XSS Sanitizer
 * Memastikan proteksi menyeluruh terhadap Cross-Site Scripting (XSS)
 */
class Security
{
    /**
     * Sanitize output to prevent XSS
     * Wajib standar industri: htmlspecialchars($data, ENT_QUOTES, 'UTF-8')
     */
    public static function escape($data): string
    {
        if ($data === null) {
            return '';
        }
        return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Recursive sanitize array data
     */
    public static function escapeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $escapedKey = self::escape($key);
            if (is_array($value)) {
                $sanitized[$escapedKey] = self::escapeArray($value);
            } else {
                $sanitized[$escapedKey] = self::escape($value);
            }
        }
        return $sanitized;
    }

    /**
     * Clean and normalize string input
     */
    public static function cleanInput(?string $data): string
    {
        if ($data === null) {
            return '';
        }
        return trim(strip_tags($data));
    }

    /**
     * Generate Hidden CSRF Input Field
     */
    public static function csrfField(): string
    {
        $token = Session::getCsrfToken();
        return '<input type="hidden" name="_csrf" value="' . self::escape($token) . '">';
    }
}
// BUG-18 FIX: Fungsi e() sudah didefinisikan di core/helpers.php.
// Duplikasi di sini dihapus untuk menghindari konflik definisi fungsi.

