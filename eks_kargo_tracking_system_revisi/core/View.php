<?php
namespace Core;

/**
 * View Renderer Engine
 * Mengelola rendering view modular dengan layout terstruktur dan proteksi XSS
 */
class View
{
    private static string $baseDir;

    public static function init(): void
    {
        self::$baseDir = dirname(__DIR__) . '/views/';
    }

    /**
     * Render view with layout
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        if (!isset(self::$baseDir)) {
            self::init();
        }

        // Extract variables to local scope
        extract($data);

        $viewFile = self::$baseDir . $view . '.php';
        if (!file_exists($viewFile)) {
            die("View file tidak ditemukan: " . htmlspecialchars($viewFile, ENT_QUOTES, 'UTF-8'));
        }

        // Render view content into buffer
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // If layout requested, render inside layout
        if ($layout !== null) {
            $layoutFile = self::$baseDir . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
        }

        echo $content;
    }

    /**
     * Render partial view (tanpa layout)
     */
    public static function partial(string $partial, array $data = []): void
    {
        if (!isset(self::$baseDir)) {
            self::init();
        }
        extract($data);
        $file = self::$baseDir . $partial . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
}
// BUG-18 FIX: Helper functions (base_url, format_rupiah, format_tanggal_indo) sudah
// didefinisikan di core/helpers.php dengan guard if (!function_exists(...)).
// Duplikasi di sini dihapus untuk menghindari inkonsistensi antar dua sumber kode yang sama.
