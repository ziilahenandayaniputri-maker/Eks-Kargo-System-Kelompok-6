<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Masuk ke Akun - KargoTrack') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=1.1.0') ?>">
</head>
<body class="bg-[#f4f7f6] min-h-screen flex flex-col justify-center">

<div class="min-h-screen flex flex-col lg:flex-row">
    <!-- Desktop Left Dark Banner (Shown on lg screens, exactly as in Screenshot 5) -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#0d282c] text-white p-16 flex-col justify-between relative overflow-hidden">
        <!-- Brand Header -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center font-bold text-2xl text-white shadow-md">
                K
            </div>
            <span class="text-2xl font-black tracking-tight text-white">KargoTrack</span>
        </div>

        <!-- Headline Hero -->
        <div class="my-auto max-w-lg">
            <h1 class="text-4xl xl:text-5xl font-black leading-tight tracking-tight mb-4 text-white">
                Pantau kargo antar-kota dari gudang sampai pintu penerima.
            </h1>
            <p class="text-slate-300 text-base leading-relaxed mb-8">
                Masuk untuk menerbitkan resi, menyusun manifest muatan, dan memperbarui checkpoint perjalanan ekspedisi logistik secara real-time.
            </p>

            <!-- Quick Track Box inside Desktop Dark Banner -->
            <div class="bg-[#12363b] border border-teal-800/40 p-5 rounded-xl shadow-lg">
                <div class="text-xs font-semibold text-slate-300 mb-2">Cek resi tanpa login</div>
                <form action="<?= base_url('tracking/publicTrack') ?>" method="GET" class="flex gap-2 mb-3">
                    <input type="text" name="resi" value="KT-CRB-261030-0047" placeholder="Masukkan nomor resi" class="flex-1 px-3 py-2 text-xs rounded bg-white text-slate-900 border-0 focus:ring-2 focus:ring-amber-500 font-mono">
                    <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded transition shadow">
                        Lacak
                    </button>
                </form>
                
                <!-- Live Mini Checkpoint Preview -->
                <div class="text-[11px] text-slate-300 space-y-1.5 pt-2 border-t border-teal-800/60">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                        <span class="text-slate-400">Resi diterbitkan · Cirebon</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-teal-400"></span>
                        <span class="text-slate-400">Dimuat ke truk B 9127 KCD</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        <span class="text-amber-300 font-medium">Tiba di hub Bekasi · 30 Okt, 20:05</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="text-xs text-slate-400">
            &copy; <?= date('Y') ?> KargoTrack Logistics Systems · Cabang Utama Cirebon
        </div>
    </div>

    <!-- Login Right Half / Mobile Full Screen (Matching Screenshot 1) -->
    <div class="flex-1 flex flex-col justify-center items-center p-6 sm:p-12 lg:p-16">
        <div class="w-full max-w-md">
            <!-- Mobile Brand Header (Hidden on lg) -->
            <div class="flex items-center gap-2.5 mb-8 lg:hidden">
                <div class="w-9 h-9 bg-amber-500 rounded-lg flex items-center justify-center font-bold text-xl text-white shadow">
                    K
                </div>
                <span class="text-2xl font-black tracking-tight text-teal-900">KargoTrack</span>
            </div>

            <!-- Title & Subtitle -->
            <div class="mb-6">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-2">Masuk ke akun</h2>
                <p class="text-sm text-slate-500">Gunakan akun petugas yang diberikan admin cabang.</p>
            </div>

            <!-- Flash Error Message -->
            <?php if (\Core\Session::hasFlash('error')): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><?= e(\Core\Session::getFlash('error')) ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="<?= base_url('auth/authenticate') ?>" method="POST" class="space-y-4">
                <?= \Core\Security::csrfField() ?>

                <div class="form-group">
                    <label class="form-label" for="login-email">Email atau ID petugas</label>
                    <input type="text" id="login-email" name="email" value="rizqi@kargotrack.id" required class="form-input" placeholder="contoh: rizqi@kargotrack.id">
                </div>

                <div class="form-group">
                    <label class="form-label" for="login-password">Kata sandi</label>
                    <input type="password" id="login-password" name="password" value="admin123" required class="form-input" placeholder="••••••••••••">
                </div>

                <div class="flex items-center justify-between text-xs pt-1 pb-2">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" checked class="rounded border-slate-300 text-teal-700 focus:ring-teal-700">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                    <a href="javascript:alert('Silakan hubungi administrator IT Cabang Cirebon untuk reset kata sandi.');" class="text-teal-700 hover:text-teal-800 font-semibold underline">
                        Lupa kata sandi?
                    </a>
                </div>

                <button type="submit" class="w-full py-3 bg-[#0d5c58] hover:bg-[#094744] text-white font-bold rounded-lg transition shadow-md text-sm">
                    Masuk
                </button>
            </form>

            <!-- Card: Cek Resi Tanpa Login (Matching Screenshot 1) -->
            <div class="mt-8 p-5 bg-[#ebf2f1] border border-teal-100 rounded-xl shadow-sm">
                <div class="text-xs font-bold text-slate-700 mb-3">Cek resi tanpa login</div>
                <form action="<?= base_url('tracking/publicTrack') ?>" method="GET" class="space-y-3">
                    <input type="text" name="resi" value="KT-CRB-261030-0047" placeholder="Masukkan nomor resi" class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-900 font-mono focus:outline-none focus:border-amber-500">
                    <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-lg transition shadow">
                        Lacak
                    </button>
                </form>
            </div>

            <!-- Footer Help Note -->
            <p class="text-center text-xs text-slate-500 mt-6">
                Belum punya akun? Hubungi admin cabang Cirebon.
            </p>
        </div>
    </div>
</div>

</body>
</html>

