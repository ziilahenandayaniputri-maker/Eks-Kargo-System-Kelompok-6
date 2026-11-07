<?php
/**
 * Global Helpers for KargoTrack
 * Declared in Global Namespace for easy and clean access across views & controllers
 */

if (!function_exists('e')) {
    function e($data): string {
        return \Core\Security::escape($data);
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = str_replace('\\', '/', dirname($scriptName));
        
        // Jika request tidak mengandung /public, gunakan base root project agar URL rapi
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_ends_with($dir, '/public') && !str_contains($requestUri, '/public')) {
            $dir = substr($dir, 0, -7);
        }
        
        $base = rtrim($protocol . $host . $dir, '/');
        $path = ltrim($path, '/');
        
        return empty($path) ? $base : $base . '/' . $path;
    }
}

if (!function_exists('format_rupiah')) {
    function format_rupiah($nominal): string {
        return 'Rp ' . number_format((float)$nominal, 0, ',', '.');
    }
}

if (!function_exists('format_tanggal_indo')) {
    function format_tanggal_indo(?string $datetime, bool $withTime = true): string {
        if (!$datetime) return '-';
        $timestamp = strtotime($datetime);
        if (!$timestamp) return $datetime;

        $bulanIndo = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'
        ];

        $tgl = date('d', $timestamp);
        $bln = (int)date('m', $timestamp);
        $thn = date('Y', $timestamp);
        $jam = date('H:i', $timestamp);

        $bulanStr = $bulanIndo[$bln] ?? date('M', $timestamp);
        return $withTime ? "{$tgl} {$bulanStr} {$thn}, {$jam}" : "{$tgl} {$bulanStr} {$thn}";
    }
}

if (!function_exists('current_route')) {
    function current_route(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = str_replace('\\', '/', dirname($scriptName));

        if ($scriptDir !== '/' && !empty($scriptDir) && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        } else {
            $parentDir = str_replace('\\', '/', dirname($scriptDir));
            if ($parentDir !== '/' && !empty($parentDir) && strpos($uri, $parentDir) === 0) {
                $uri = substr($uri, strlen($parentDir));
            }
        }

        $trimmed = trim($uri, '/');
        if (str_starts_with($trimmed, 'public/')) {
            $trimmed = substr($trimmed, 7);
        }

        $segments = explode('/', $trimmed);
        $primary = $segments[0] ?? '';

        if (empty($primary) || $primary === 'public') {
            return 'wilayah';
        }

        return strtolower($primary);
    }
}
