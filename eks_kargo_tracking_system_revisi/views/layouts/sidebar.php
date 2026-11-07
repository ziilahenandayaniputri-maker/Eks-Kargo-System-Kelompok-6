<?php
use Core\Session;
$currentUser = Session::getUser() ?? ['nama' => 'Rizqi', 'role' => 'Admin Gudang', 'cabang' => 'Cabang Cirebon'];
$activeNav = current_route();
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">K</div>
        <div class="brand-text">KargoTrack</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Modul Utama</div>
        
        <a href="<?= base_url('wilayah') ?>" class="nav-link <?= ($activeNav === 'wilayah') ? 'active' : '' ?>">
            <svg class="nav-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <span>Wilayah, Rute & Tarif</span>
        </a>

        <a href="<?= base_url('resi') ?>" class="nav-link <?= ($activeNav === 'resi') ? 'active' : '' ?>">
            <svg class="nav-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>Resi & Paket</span>
        </a>

        <a href="<?= base_url('tracking') ?>" class="nav-link <?= ($activeNav === 'tracking') ? 'active' : '' ?>">
            <svg class="nav-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>Tracking & Serah Terima</span>
        </a>

        <a href="<?= base_url('manifest') ?>" class="nav-link <?= ($activeNav === 'manifest') ? 'active' : '' ?>">
            <svg class="nav-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
            <span>Manifest Truk Kargo</span>
        </a>

        <div class="nav-section-title" style="margin-top: 16px;">Pelacakan Publik</div>
        <a href="<?= base_url('tracking/publicTrack') ?>" target="_blank" class="nav-link">
            <svg class="nav-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
            <span>Portal Lacak Resi</span>
        </a>
    </nav>

    <div class="sidebar-user">
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($currentUser['nama'] ?? 'R', 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= e($currentUser['nama'] ?? 'Rizqi') ?></div>
                <div class="user-role"><?= e($currentUser['role'] ?? 'Admin Gudang') ?></div>
            </div>
        </div>
        <a href="<?= base_url('auth/logout') ?>" class="logout-btn" title="Keluar / Logout">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
        </a>
    </div>
</aside>
