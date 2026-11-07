<?php
namespace Core;

/**
 * Abstract Base Controller
 * Menyediakan fungsionalitas dasar Controller dalam pola MVC
 */
abstract class Controller
{
    /**
     * Render View HTML
     */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        View::render($view, $data, $layout);
    }

    /**
     * Send JSON Response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $path): void
    {
        $target = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
            ? $path
            : base_url($path);

        header("Location: {$target}");
        exit;
    }

    /**
     * Proteksi Otentikasi Sesi (Wajib Login)
     */
    protected function requireAuth(): array
    {
        Session::start();
        if (!Session::isLoggedIn()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'], 401);
            }
            Session::setFlash('error', 'Silakan login terlebih dahulu untuk mengakses menu ini.');
            $this->redirect('auth/login');
        }
        return Session::getUser();
    }

    /**
     * Deteksi apakah permintaan adalah AJAX / Fetch
     */
    protected function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
    }

    /**
     * Validasi input sederhana
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $fieldRules) {
            $value = trim($data[$field] ?? '');
            $rulesList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($rulesList as $rule) {
                if ($rule === 'required' && ($value === '' || $value === null)) {
                    $errors[$field] = "Bidang {$field} wajib diisi.";
                    break;
                }
                if ($rule === 'phone_id' && !empty($value)) {
                    // Validasi format Indonesia: mulai 08 dan 10-13 digit
                    $cleanPhone = preg_replace('/[^0-9]/', '', $value);
                    if (!preg_match('/^08[0-9]{8,11}$/', $cleanPhone)) {
                        $errors[$field] = "Nomor tidak lengkap. Isi 10-13 digit, mulai 08.";
                        break;
                    }
                }
                if ($rule === 'postal_code' && !empty($value)) {
                    // Validasi 5 digit kode pos
                    if (!preg_match('/^[0-9]{5}$/', $value)) {
                        $errors[$field] = "Kode pos harus 5 digit.";
                        break;
                    }
                }
                if ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
                    $errors[$field] = "Bidang {$field} harus berupa angka.";
                    break;
                }
                if (str_starts_with($rule, 'min:') && !empty($value)) {
                    $min = (int)substr($rule, 4);
                    if ((float)$value < $min) {
                        $errors[$field] = "Nilai {$field} minimal {$min}.";
                        break;
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * Verifikasi CSRF Token pada POST request
     */
    protected function verifyCsrf(): bool
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        return Session::verifyCsrfToken($token);
    }

    /**
     * Set Flash Message
     */
    protected function setFlash(string $key, string $message): void
    {
        Session::setFlash($key, $message);
    }
}

