<?php
namespace Core;

/**
 * Application Router & Request Dispatcher
 * Mendukung deklarasi rute eksplisit maupun dispatching MVC dinamis
 */
class App
{
    private static array $routes = [];

    /**
     * Daftarkan route GET
     */
    public static function get(string $path, string $handler): void
    {
        self::$routes['GET'][trim($path, '/')] = $handler;
    }

    /**
     * Daftarkan route POST
     */
    public static function post(string $path, string $handler): void
    {
        self::$routes['POST'][trim($path, '/')] = $handler;
    }

    /**
     * Jalankan Router
     */
    public static function run(): void
    {
        Session::start();
        
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = self::parseUri();

        // 1. Cek explicit registered route
        if (isset(self::$routes[$method][$uri])) {
            self::dispatchHandler(self::$routes[$method][$uri]);
            return;
        }

        // Cek parameterized routes (misal: tracking/detail/{no_resi})
        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $route => $handler) {
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Hilangkan full match
                    self::dispatchHandler($handler, $matches);
                    return;
                }
            }
        }

        // 2. Dynamic MVC Dispatcher: controller/action/params
        $parts = !empty($uri) ? explode('/', $uri) : [];
        $controllerName = !empty($parts[0]) ? ucfirst($parts[0]) . 'Controller' : 'WilayahController';
        $actionName = !empty($parts[1]) ? $parts[1] : 'index';
        $params = array_slice($parts, 2);

        $fullControllerClass = "Controllers\\{$controllerName}";

        if (class_exists($fullControllerClass)) {
            $controller = new $fullControllerClass();
            if (method_exists($controller, $actionName)) {
                call_user_func_array([$controller, $actionName], $params);
                return;
            }
        }

        // 3. Fallback jika rute tidak ditemukan (404)
        http_response_code(404);
        echo "<!DOCTYPE html><html><head><title>404 - Halaman Tidak Ditemukan</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'>";
        echo "<h2>404 - Halaman Tidak Ditemukan</h2>";
        echo "<p>Rute <code>" . htmlspecialchars($uri, ENT_QUOTES, 'UTF-8') . "</code> tidak tersedia di sistem KargoTrack.</p>";
        echo "<a href='" . base_url() . "' style='display:inline-block;padding:10px 20px;background:#0d5c58;color:#fff;text-decoration:none;border-radius:6px;'>Kembali ke Beranda</a>";
        echo "</body></html>";
    }

    /**
     * Dispatch String Handler "Controller@method"
     */
    private static function dispatchHandler(string $handler, array $params = []): void
    {
        [$controller, $method] = explode('@', $handler);
        $fullController = "Controllers\\{$controller}";

        if (!class_exists($fullController)) {
            die("Controller {$fullController} tidak ditemukan.");
        }

        $instance = new $fullController();
        if (!method_exists($instance, $method)) {
            die("Method {$method} tidak ditemukan pada {$fullController}.");
        }

        call_user_func_array([$instance, $method], $params);
    }

    /**
     * Parse URI dari Query Parameter atau SCRIPT_NAME
     */
    private static function parseUri(): string
    {
        if (isset($_GET['url'])) {
            $u = trim($_GET['url'], '/');
            if (strpos($u, '?') !== false) {
                $u = substr($u, 0, strpos($u, '?'));
            }
            return trim($u, '/');
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        if (strpos($requestUri, '?') !== false) {
            $requestUri = substr($requestUri, 0, strpos($requestUri, '?'));
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = str_replace('\\', '/', dirname($scriptName));

        // Cek jika scriptDir ada di awal requestUri
        if ($scriptDir !== '/' && !empty($scriptDir) && strpos($requestUri, $scriptDir) === 0) {
            $requestUri = substr($requestUri, strlen($scriptDir));
        } else {
            // Cek parent directory jika file dieksekusi dari dalam /public
            $parentDir = str_replace('\\', '/', dirname($scriptDir));
            if ($parentDir !== '/' && !empty($parentDir) && strpos($requestUri, $parentDir) === 0) {
                $requestUri = substr($requestUri, strlen($parentDir));
            }
        }

        $cleaned = trim($requestUri, '/');
        if (str_starts_with($cleaned, 'public/')) {
            $cleaned = substr($cleaned, 7);
        } elseif ($cleaned === 'public') {
            $cleaned = '';
        }

        return trim($cleaned, '/');
    }
}
