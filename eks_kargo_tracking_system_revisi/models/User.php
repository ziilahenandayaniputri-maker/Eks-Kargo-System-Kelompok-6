<?php
namespace Models;

use Core\Model;

/**
 * Model User (Otentikasi & Petugas)
 * Mengimplementasikan OOP Encapsulation & password_verify
 */
class User extends Model
{
    protected string $table = 'users';

    /**
     * Cari user berdasarkan email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->firstWhere(['email' => $email]);
    }

    /**
     * Verifikasi Kredensial Login
     * BUG-22 FIX: Selalu jalankan password_verify agar tidak ada perbedaan waktu respons
     * antara email tidak ditemukan vs password salah (mencegah user enumeration timing attack)
     */
    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        // Tetap jalankan password_verify meski user tidak ditemukan agar timing konsisten
        $dummyHash = '$2y$10$invalidhashpadding.invalidhashpadding.invalidhashpaddi';
        $hashToVerify = $user ? $user['password'] : $dummyHash;

        if (!password_verify($password, $hashToVerify)) {
            return null;
        }

        if (!$user) {
            return null;
        }

        // Sembunyikan password hash dari object user yang disimpan di sesi
        unset($user['password']);
        return $user;
    }
}

