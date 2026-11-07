<?php
use Core\Session;
$currentUser = Session::getUser() ?? ['nama' => 'Rizqi', 'role' => 'Admin Gudang'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'KargoTrack - Sistem Informasi Pelacakan Kargo') ?></title>
    
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (via local-ready CDN fallback for maximum UI fidelity) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'kargo-dark': '#0d282c',
                        'kargo-teal': '#0d5c58',
                        'kargo-teal-hover': '#094744',
                        'kargo-amber': '#f59e0b',
                        'kargo-amber-hover': '#d97706',
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=1.1.0') ?>">
    
    <!-- JsBarcode for scannable barcodes on receipts -->
    <script src="<?= base_url('assets/js/jsbarcode.min.js?v=1.1.0') ?>"></script>
</head>
<body class="bg-[#f4f7f6] text-slate-800 antialiased">

<div class="app-container">
    <!-- Desktop Sidebar -->
    <?php require __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="main-wrapper">
        <!-- Top Header Bar -->
        <header class="top-header">
            <div class="flex items-center gap-3">
                <!-- Mobile brand icon -->
                <div class="md:hidden flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-amber-500 text-white font-bold flex items-center justify-center text-base">K</span>
                    <span class="font-bold text-lg text-teal-900">KargoTrack</span>
                </div>
                <div class="hidden md:block page-title-box">
                    <h1><?= e($pageTitle ?? 'KargoTrack') ?></h1>
                    <p>Cabang Cirebon · Sistem Informasi Pelacakan Kargo & Verifikasi Resi</p>
                </div>
            </div>

            <!-- Quick Action & Search in Top Header -->
            <div class="flex items-center gap-3">
                <a href="<?= base_url('tracking/publicTrack') ?>" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-teal-800 bg-teal-50 border border-teal-200 rounded-md hover:bg-teal-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span>Lacak Cepat</span>
                </a>
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-semibold text-slate-700"><?= e($currentUser['nama'] ?? 'Rizqi') ?></div>
                    <div class="text-[11px] text-slate-400">Cabang Cirebon (CRB)</div>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="content-body">
            <!-- Flash Message Alerts -->
            <?php if (Session::hasFlash('success')): ?>
                <div class="mb-5 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span><?= e(Session::getFlash('success')) ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (Session::hasFlash('error')): ?>
                <div class="mb-5 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span><?= e(Session::getFlash('error')) ?></span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-lg leading-none">&times;</button>
                </div>
            <?php endif; ?>

            <!-- View Content Injection -->
            <?= $content ?? '' ?>
        </main>

        <!-- Mobile Bottom Nav -->
        <?php require __DIR__ . '/bottom_nav.php'; ?>
    </div>
</div>

<!-- Global Notification Toast Container -->
<div id="toast-container" class="fixed bottom-20 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

<!-- Vanilla JS Application Logic -->
<script>
    window.BASE_URL = '<?= base_url() ?>';
</script>
<script src="<?= base_url('assets/js/app.js?v=1.1.0') ?>"></script>
</body>
</html>

