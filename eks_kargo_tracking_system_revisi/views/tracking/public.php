<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Lacak Resi Kargo Publik - KargoTrack') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=1.1.0') ?>">
</head>
<body class="bg-[#f4f7f6] text-slate-800 min-h-screen">

<!-- Public Top Navigation -->
<header class="bg-white border-b border-slate-200 py-4 px-6 sticky top-0 z-30 shadow-sm">
    <div class="max-w-4xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-amber-500 text-white font-bold flex items-center justify-center text-lg shadow">K</span>
            <span class="text-xl font-black text-teal-900 tracking-tight">KargoTrack</span>
            <span class="hidden sm:inline-block text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded ml-2">Portal Pelacakan Publik</span>
        </div>
        <a href="<?= base_url('auth/login') ?>" class="text-xs font-semibold text-teal-800 hover:text-teal-950 bg-teal-50 hover:bg-teal-100 border border-teal-200 px-3 py-1.5 rounded-lg transition">
            Masuk Petugas &rarr;
        </a>
    </div>
</header>

<main class="max-w-4xl mx-auto px-4 py-8">
    <!-- Tracking Input Search Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-8 text-center max-w-xl mx-auto">
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mb-2">Lacak Posisi Kargo Anda</h1>
        <p class="text-xs text-slate-500 mb-6">Masukkan nomor resi pengiriman untuk mengetahui timeline perjalanan real-time.</p>
        
        <form action="<?= base_url('tracking/publicTrack') ?>" method="GET" class="flex gap-2">
            <input type="text" name="resi" value="<?= e($noResi ?: 'KT-CRB-261030-0047') ?>" placeholder="contoh: KT-CRB-261030-0047" required class="flex-1 px-4 py-2.5 text-xs sm:text-sm font-mono border border-slate-300 rounded-lg focus:outline-none focus:border-amber-500 uppercase">
            <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs sm:text-sm rounded-lg transition shadow">
                Lacak
            </button>
        </form>
    </div>

    <?php if ($resi): ?>
        <!-- Resi Summary Box -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <span class="text-xs font-mono font-bold text-teal-800 bg-teal-50 px-2.5 py-1 rounded border border-teal-200"><?= e($resi['no_resi']) ?></span>
                    <h2 class="text-lg font-black text-slate-900 mt-2"><?= e($resi['asal_nama']) ?> &rarr; <?= e($resi['tujuan_nama']) ?></h2>
                    <div class="text-xs text-slate-400 mt-0.5">Layanan: <span class="font-semibold text-slate-700"><?= e($resi['layanan']) ?></span> · <?= e($resi['isi_paket']) ?></div>
                </div>
                <div class="text-left sm:text-right">
                    <span class="badge <?= ($resi['status_pengiriman'] === 'Diterima penerima') ? 'badge-success' : 'badge-warning' ?> text-sm px-3 py-1 font-bold">
                        <?= e($resi['status_pengiriman']) ?>
                    </span>
                    <div class="text-xs text-slate-400 mt-1"><?= e($resi['berat_dikenakan']) ?> kg · <?= e($resi['jumlah_koli']) ?> koli</div>
                </div>
            </div>

            <!-- Visual CSS Timeline -->
            <div class="pt-6">
                <div class="timeline-container">
                    <?php 
                    $totalCp = count($checkpoints);
                    foreach ($checkpoints as $idx => $cp): 
                        $isLast = ($idx === $totalCp - 1);
                        $isDelivered = ($cp['status'] === 'Diterima penerima');
                        $markerClass = ($isLast && !$isDelivered) ? 'active' : 'completed';
                    ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?= $markerClass ?>"></div>
                            <div class="timeline-content">
                                <div class="timeline-title"><?= e($cp['status']) ?></div>
                                <div class="timeline-meta">
                                    <?= format_tanggal_indo($cp['waktu'], true) ?> · <?= e($cp['lokasi']) ?>
                                    <?php if (!empty($cp['keterangan'])): ?>
                                        <div class="text-slate-600 font-medium mt-0.5"><?= e($cp['keterangan']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Bukti POD jika ada -->
                                <?php if ($cp['foto_bukti'] || $cp['ttd_digital']): ?>
                                    <div class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-lg flex flex-wrap gap-4 items-center">
                                        <?php if ($cp['foto_bukti']): ?>
                                            <div>
                                                <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Foto Bukti Penerimaan</div>
                                                <img src="<?= base_url('uploads/pod/' . $cp['foto_bukti']) ?>" alt="POD" class="w-20 h-20 object-cover rounded border border-slate-300 shadow-sm">
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($cp['ttd_digital']): ?>
                                            <div>
                                                <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Tanda Tangan Penerima</div>
                                                <img src="<?= $cp['ttd_digital'] ?>" alt="TTD" class="h-16 w-32 object-contain bg-white border border-slate-300 rounded p-1">
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($cp['nama_penerima']): ?>
                                            <div class="text-xs">
                                                <span class="text-slate-400">Penerima Resmi:</span>
                                                <div class="font-bold text-slate-800"><?= e($cp['nama_penerima']) ?></div>
                                                <span class="text-[10px] text-emerald-700 font-medium">Paket telah diserahterimakan dengan sukses.</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    <?php elseif (!empty($noResi)): ?>
        <div class="bg-white rounded-xl border border-rose-200 p-8 text-center max-w-md mx-auto shadow-sm">
            <svg class="w-12 h-12 text-rose-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <h3 class="text-base font-bold text-slate-800 mb-1">Nomor Resi Tidak Ditemukan</h3>
            <p class="text-xs text-slate-500 mb-4">Pastikan nomor resi <code><?= e($noResi) ?></code> sudah benar atau hubungi admin kargo cabang pengirim.</p>
            <a href="<?= base_url('tracking/publicTrack?resi=KT-CRB-261030-0047') ?>" class="text-xs text-teal-800 font-bold underline">
                Coba Lacak Sampel Resi: KT-CRB-261030-0047
            </a>
        </div>
    <?php endif; ?>
</main>

<footer class="text-center py-6 text-xs text-slate-400 border-t border-slate-200 bg-white mt-12">
    &copy; <?= date('Y') ?> KargoTrack Logistics Systems. Seluruh Hak Cipta Dilindungi.
</footer>

</body>
</html>

