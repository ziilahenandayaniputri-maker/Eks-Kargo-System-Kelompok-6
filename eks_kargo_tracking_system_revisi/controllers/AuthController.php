<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Models\User;
use Models\Resi;
use Models\Tracking;

/**
 * Controller Otentikasi & Keamanan Sesi
 */
class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Tampilan Login (Desktop Split Screen & Mobile Friendly)
     */
    public function login(): void
    {
        // Jika sudah login, langsung alihkan ke dashboard / wilayah
        if (Session::isLoggedIn()) {
            $this->redirect('wilayah');
        }

        // Ambil data sampel resi aktif untuk widget "Cek resi tanpa login" di halaman login
        $resiModel = new Resi();
        $sampleResi = $resiModel->firstWhere(['status_pengiriman' => 'Dalam perjalanan']) 
                      ?? $resiModel->all('*', 'id DESC')[0] ?? null;

        $latestCheckpoint = null;
        if ($sampleResi) {
            $trackingModel = new Tracking();
            $latestCheckpoint = $trackingModel->getLatestCheckpoint($sampleResi['id']);
        }

        $this->view('auth/login', [
            'sampleResi' => $sampleResi,
            'latestCheckpoint' => $latestCheckpoint,
            'pageTitle' => 'Masuk ke Akun - KargoTrack'
        ], null); // Render tanpa layout dashboard utama
    }

    /**
     * Proses Otentikasi Petugas
     */
    public function authenticate(): void
    {
        // BUG-01: Verifikasi CSRF pada setiap POST
        if (!$this->verifyCsrf()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Token keamanan tidak valid. Muat ulang halaman dan coba lagi.'], 403);
            }
            $this->setFlash('error', 'Token keamanan tidak valid.');
            $this->redirect('auth/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Email/ID dan kata sandi wajib diisi.'], 422);
            }
            $this->setFlash('error', 'Email/ID dan kata sandi wajib diisi.');
            $this->redirect('auth/login');
        }

        // BUG-22: Anti timing-attack — selalu jalankan password_verify meski user tidak ditemukan
        $user = $this->userModel->verifyCredentials($email, $password);

        if (!$user) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Email atau kata sandi tidak valid.'], 401);
            }
            $this->setFlash('error', 'Kombinasi email atau kata sandi tidak sesuai.');
            $this->redirect('auth/login');
        }

        // Set session user dengan session_regenerate_id(true)
        Session::setUser($user);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => 'Login berhasil! Mengalihkan...',
                'redirect' => base_url('wilayah')
            ]);
        }

        $this->setFlash('success', 'Selamat datang kembali, ' . $user['nama'] . '!');
        $this->redirect('wilayah');
    }

    /**
     * Logout Petugas
     */
    public function logout(): void
    {
        Session::destroy();
        $this->redirect('auth/login');
    }
}

