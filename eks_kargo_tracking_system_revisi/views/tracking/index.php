<?php
// views/tracking/index.php
$r = $resi ?? [];
$cpList = $checkpoints ?? [];
$latestCp = end($cpList);
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Tracking & serah terima</h2>
        <div class="flex items-center gap-2 mt-1">
            <span class="font-mono font-bold text-xs sm:text-sm text-teal-900"><?= e($r['no_resi'] ?? 'KT-CRB-261030-0047') ?></span>
            <span class="text-slate-300">·</span>
            <span class="text-xs sm:text-sm text-slate-600 font-medium"><?= e(($r['asal_nama'] ?? 'Cirebon') . ' → ' . ($r['tujuan_nama'] ?? 'Jakarta')) ?></span>
        </div>
    </div>
    
    <!-- Action buttons & Resi Switcher -->
    <div class="flex items-center gap-2">
        <select onchange="window.location.href = window.BASE_URL + '/tracking?resi=' + encodeURIComponent(this.value)" class="form-select text-xs py-1.5 w-48">
            <?php foreach ($allResi as $item): ?>
                <option value="<?= e($item['no_resi']) ?>" <?= ($item['no_resi'] === ($r['no_resi'] ?? '')) ? 'selected' : '' ?>>
                    <?= e($item['no_resi']) ?> (<?= e($item['status_pengiriman']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button type="button" onclick="document.getElementById('checkpoint-status').focus()" class="btn btn-primary btn-sm bg-[#0d5c58] hover:bg-[#094744] whitespace-nowrap">
            + Checkpoint baru
        </button>
    </div>
</div>

<!-- Two-Column Grid: Left = Visual Timeline, Right = Tambah Checkpoint Form -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    
    <!-- LEFT COLUMN: PERJALANAN PAKET & VISUAL CSS TIMELINE (Col 7) -->
    <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        
        <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Perjalanan paket</h3>
                <p class="text-xs text-slate-400">Pembaruan lokasi dan status logistik secara berurutan</p>
            </div>
            
            <!-- Dynamic Status Badge -->
            <?php
            $statusCurr = $r['status_pengiriman'] ?? 'Resi diterbitkan';
            $badgeClass = 'badge-info';
            if ($statusCurr === 'Diterima penerima') {
                $badgeClass = 'badge-success';
            } elseif ($statusCurr === 'Dalam perjalanan' || str_contains($statusCurr, 'Tiba')) {
                $badgeClass = 'badge-warning';
            }
            ?>
            <span class="badge <?= $badgeClass ?> text-xs px-3 py-1 font-bold">
                <?= e($statusCurr) ?>
            </span>
        </div>

        <!-- Vertical Visual Timeline CSS -->
        <div class="timeline-container">
            <?php
            // List of milestones to show
            // If actual checkpoints exist, display them; followed by future milestone placeholders
            $hasDelivered = false;
            $podPhoto = null;
            $podSignature = null;
            $podRecipient = null;

            if (!empty($cpList)):
                $totalCp = count($cpList);
                foreach ($cpList as $idx => $cp):
                    $isLast = ($idx === $totalCp - 1);
                    $isDeliveredCp = ($cp['status'] === 'Diterima penerima');
                    if ($isDeliveredCp) {
                        $hasDelivered = true;
                        $podPhoto = $cp['foto_bukti'];
                        $podSignature = $cp['ttd_digital'];
                        $podRecipient = $cp['nama_penerima'];
                    }

                    // Marker style: completed (double teal) or active (glowing amber)
                    $markerClass = ($isLast && !$isDeliveredCp) ? 'active' : 'completed';
                    if ($isDeliveredCp) $markerClass = 'completed';
            ?>
                <div class="timeline-item">
                    <div class="timeline-marker <?= $markerClass ?>"></div>
                    <div class="timeline-content">
                        <div class="timeline-title"><?= e($cp['status']) ?></div>
                        <div class="timeline-meta">
                            <?= format_tanggal_indo($cp['waktu'], true) ?> · <?= e($cp['lokasi']) ?>
                            <?php if (!empty($cp['petugas'])): ?>
                                · Petugas: <?= e($cp['petugas']) ?>
                            <?php endif; ?>
                            <?php if (!empty($cp['keterangan'])): ?>
                                · <span class="text-slate-600 font-medium"><?= e($cp['keterangan']) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- If POD Proof attached on this checkpoint -->
                        <?php if ($cp['foto_bukti'] || $cp['ttd_digital']): ?>
                            <div class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-lg flex flex-wrap gap-4 items-center">
                                <?php if ($cp['foto_bukti']): ?>
                                    <div>
                                        <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Foto Serah Terima</div>
                                        <a href="<?= base_url('uploads/pod/' . $cp['foto_bukti']) ?>" target="_blank">
                                            <img src="<?= base_url('uploads/pod/' . $cp['foto_bukti']) ?>" alt="Bukti POD" class="w-20 h-20 object-cover rounded border border-slate-300 shadow-sm hover:scale-105 transition">
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if ($cp['ttd_digital']): ?>
                                    <div>
                                        <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Tanda Tangan Digital</div>
                                        <div class="p-1 bg-white border border-slate-300 rounded shadow-sm">
                                            <img src="<?= $cp['ttd_digital'] ?>" alt="TTD Digital" class="h-16 w-32 object-contain">
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($cp['nama_penerima']): ?>
                                    <div class="text-xs">
                                        <span class="text-slate-400">Penerima Resmi:</span>
                                        <div class="font-bold text-slate-800"><?= e($cp['nama_penerima']) ?></div>
                                        <span class="text-[10px] text-emerald-700 font-medium">✓ Serah Terima Terverifikasi</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
                endforeach; 
            endif;
            ?>

            <!-- Future/Upcoming Milestone Placeholders if not yet delivered -->
            <?php if (!$hasDelivered): ?>
                <?php if (!str_contains($statusCurr, 'Dikirim')): ?>
                <div class="timeline-item opacity-60">
                    <div class="timeline-marker upcoming"></div>
                    <div class="timeline-content">
                        <div class="timeline-title text-slate-500">Dikirim ke alamat penerima</div>
                        <div class="timeline-meta">Estimasi <?= date('d M Y', strtotime('+1 day')) ?> · Armada Kurir Hub Tujuan</div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="timeline-item opacity-60">
                    <div class="timeline-marker upcoming"></div>
                    <div class="timeline-content">
                        <div class="timeline-title text-slate-500">Diterima penerima</div>
                        <div class="timeline-meta">Menunggu serah terima & bukti POD</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Package Meta Card on Bottom of Timeline -->
        <div class="mt-8 pt-4 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-2.5 bg-slate-50 rounded-lg">
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Pengirim</span>
                <span class="font-bold text-slate-800"><?= e($r['nama_pengirim'] ?? '-') ?></span>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-lg">
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Penerima</span>
                <span class="font-bold text-slate-800"><?= e($r['nama_penerima'] ?? '-') ?></span>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-lg">
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Isi Muatan</span>
                <span class="font-bold text-slate-800 truncate block"><?= e($r['isi_paket'] ?? '-') ?></span>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-lg">
                <span class="text-slate-400 block text-[10px] uppercase font-semibold">Total Berat</span>
                <span class="font-bold text-teal-800"><?= e($r['berat_dikenakan'] ?? 0) ?> kg (<?= e($r['jumlah_koli'] ?? 1) ?> koli)</span>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN: FORM TAMBAH CHECKPOINT (Col 5) -->
    <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
            <span>Tambah checkpoint</span>
            <span class="text-[11px] font-mono text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                <?= e($r['no_resi'] ?? '') ?>
            </span>
        </h3>

        <form id="form-checkpoint" action="<?= base_url('tracking/storeCheckpoint') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="resi_id" value="<?= e($r['id'] ?? 1) ?>">

            <!-- Status Dropdown -->
            <div class="form-group mb-0">
                <label class="form-label text-xs" for="checkpoint-status">Status</label>
                <select id="checkpoint-status" name="status" class="form-select text-xs font-semibold" required onchange="togglePODFields(this.value)">
                    <option value="Diterima penerima">Diterima penerima</option>
                    <option value="Dikirim ke alamat penerima">Dikirim ke alamat penerima</option>
                    <option value="Tiba di hub Bekasi">Tiba di hub Bekasi</option>
                    <option value="Tiba di hub Jakarta">Tiba di hub Jakarta</option>
                    <option value="Dimuat ke truk B 9127 KCD">Dimuat ke truk B 9127 KCD</option>
                    <option value="Paket diterima gudang">Paket diterima gudang</option>
                </select>
            </div>

            <!-- Lokasi & Waktu -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs" for="checkpoint-lokasi">Lokasi</label>
                    <input type="text" id="checkpoint-lokasi" name="lokasi" value="<?= e($r['tujuan_nama'] ?? 'Jakarta Selatan') ?>" required class="form-input text-xs" placeholder="misal: Jakarta Selatan">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs" for="checkpoint-waktu">Waktu</label>
                    <input type="datetime-local" id="checkpoint-waktu" name="waktu" value="<?= date('Y-m-d\TH:i') ?>" required class="form-input text-xs font-mono">
                </div>
            </div>

            <!-- Nama Penerima (Active when status is Diterima penerima) -->
            <div class="form-group mb-0" id="group-nama-penerima">
                <label class="form-label text-xs" for="checkpoint-penerima">Nama penerima</label>
                <input type="text" id="checkpoint-penerima" name="nama_penerima" value="<?= e($r['nama_penerima'] ?? 'Dewi Lestari') ?>" class="form-input text-xs" placeholder="Nama yang menerima paket">
            </div>

            <!-- Keterangan -->
            <div class="form-group mb-0">
                <label class="form-label text-xs" for="checkpoint-keterangan">Keterangan / Catatan Serah Terima</label>
                <input type="text" id="checkpoint-keterangan" name="keterangan" value="Paket diserahterimakan dalam kondisi baik dan tersegel utuh." class="form-input text-xs" placeholder="Keterangan kondisi paket...">
            </div>

            <!-- Drag & Drop Bukti Serah Terima (POD) -->
            <div class="form-group mb-0" id="group-pod-upload">
                <label class="form-label text-xs">Bukti serah terima</label>
                <div id="dropzone-pod" class="dropzone-box" onclick="document.getElementById('input-pod-file').click()">
                    <input type="file" id="input-pod-file" name="foto_bukti" accept="image/png,image/jpeg,image/webp" class="hidden">
                    <svg class="w-6 h-6 text-slate-400 mx-auto mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <div class="text-xs text-slate-600 font-medium">Seret foto ke sini atau pilih file</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">JPG/PNG, maks 5 MB</div>
                </div>

                <!-- Chips representation: foto_1.jpg, ttd.png 2 file siap diunggah (Matching Screenshot 3) -->
                <div id="pod-chips-container" class="upload-chips">
                    <span class="upload-chip">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span id="chip-filename">foto_1.jpg</span>
                    </span>
                    <span class="upload-chip">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        <span>ttd.png</span>
                    </span>
                    <span class="text-[11px] text-slate-500 self-center">2 file siap diunggah</span>
                </div>
            </div>

            <!-- HTML5 Canvas Digital Signature Pad -->
            <div class="form-group mb-0" id="group-canvas-ttd">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="form-label text-xs mb-0">Tanda Tangan Digital Penerima</label>
                    <button type="button" onclick="clearSignaturePad()" class="text-[11px] text-rose-600 hover:text-rose-800 font-semibold">
                        Bersihkan
                    </button>
                </div>
                <div class="signature-pad-wrapper">
                    <canvas id="signature-canvas" class="signature-canvas"></canvas>
                </div>
                <input type="hidden" name="ttd_digital" id="input-ttd-base64">
                <div class="text-[10px] text-slate-400 mt-1">Goreskan tanda tangan penerima pada kotak di atas.</div>
            </div>

            <!-- CTA Submit Button -->
            <button type="submit" class="w-full py-3 bg-[#0d5c58] hover:bg-[#094744] text-white font-bold rounded-lg transition shadow-md text-sm">
                Simpan checkpoint
            </button>
        </form>
    </div>

</div>

<script>
    // Canvas Signature Pad Logic
    let canvas, ctx, isDrawing = false;

    window.addEventListener('DOMContentLoaded', () => {
        canvas = document.getElementById('signature-canvas');
        if (canvas) {
            ctx = canvas.getContext('2d');
            resizeCanvas();

            // Draw initial placeholder wave signature if empty
            drawSampleSignature();

            // Touch & Mouse Event Listeners
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseleave', stopDrawing);

            canvas.addEventListener('touchstart', (e) => {
                e.preventDefault();
                const touch = e.touches[0];
                const mouseEvent = new MouseEvent('mousedown', {
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
                canvas.dispatchEvent(mouseEvent);
            });

            canvas.addEventListener('touchmove', (e) => {
                e.preventDefault();
                const touch = e.touches[0];
                const mouseEvent = new MouseEvent('mousemove', {
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
                canvas.dispatchEvent(mouseEvent);
            });

            canvas.addEventListener('touchend', (e) => {
                e.preventDefault();
                const mouseEvent = new MouseEvent('mouseup', {});
                canvas.dispatchEvent(mouseEvent);
            });
        }

        // Drag & Drop File Upload
        const dropzone = document.getElementById('dropzone-pod');
        const fileInput = document.getElementById('input-pod-file');

        if (dropzone && fileInput) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzone.classList.add('drag-over');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('drag-over');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    updateFileChip(files[0].name);
                }
            });

            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    updateFileChip(fileInput.files[0].name);
                }
            });
        }

        // Inisialisasi tampilan bidang POD sesuai status terpilih
        const cpStatusSelect = document.getElementById('checkpoint-status');
        if (cpStatusSelect) {
            togglePODFields(cpStatusSelect.value);
        }
    });

    function resizeCanvas() {
        if (!canvas) return;
        canvas.width = canvas.parentElement.clientWidth;
        canvas.height = 120;
        ctx.strokeStyle = '#0d5c58';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
    }

    function drawSampleSignature() {
        ctx.strokeStyle = '#0d5c58';
        ctx.lineWidth = 2.5;
        ctx.beginPath();
        ctx.moveTo(30, 70);
        ctx.bezierCurveTo(60, 20, 100, 90, 140, 50);
        ctx.bezierCurveTo(180, 20, 220, 80, 260, 40);
        ctx.stroke();
        document.getElementById('input-ttd-base64').value = canvas.toDataURL();
    }

    function startDrawing(e) {
        isDrawing = true;
        const rect = canvas.getBoundingClientRect();
        ctx.beginPath();
        ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
    }

    function draw(e) {
        if (!isDrawing) return;
        const rect = canvas.getBoundingClientRect();
        ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
        ctx.stroke();
    }

    function stopDrawing() {
        if (isDrawing) {
            isDrawing = false;
            document.getElementById('input-ttd-base64').value = canvas.toDataURL();
        }
    }

    function clearSignaturePad() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('input-ttd-base64').value = '';
    }

    function updateFileChip(filename) {
        const chip = document.getElementById('chip-filename');
        if (chip) chip.textContent = filename;
    }

    function togglePODFields(status) {
        const isDelivered = (status === 'Diterima penerima');
        document.getElementById('group-nama-penerima').style.display = isDelivered ? 'block' : 'none';
        document.getElementById('group-pod-upload').style.display = isDelivered ? 'block' : 'none';
        document.getElementById('group-canvas-ttd').style.display = isDelivered ? 'block' : 'none';
    }
</script>

