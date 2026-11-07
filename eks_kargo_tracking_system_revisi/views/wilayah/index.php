<?php
// views/wilayah/index.php
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Wilayah, Rute & Tarif</h2>
        <p class="text-xs sm:text-sm text-slate-500">Cabang Cirebon · kelola area layanan dan hitung ongkir</p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="openModal('modal-tambah-wilayah')" class="btn btn-outline btn-sm">
            + Tambah wilayah
        </button>
        <button type="button" onclick="openModal('modal-tambah-rute')" class="btn btn-primary btn-sm bg-[#0d5c58] hover:bg-[#094744]">
            + Tambah rute
        </button>
    </div>
</div>

<!-- KPI Mini Summary Row -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Kota</div>
        <div class="text-xl font-black text-slate-800 mt-0.5"><?= count($wilayahList) ?></div>
        <div class="text-[10px] text-teal-700 font-medium">10 Cabang & Hub Utama</div>
    </div>
    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Rute Aktif</div>
        <div class="text-xl font-black text-teal-700 mt-0.5"><?= count(array_filter($ruteList, fn($r) => $r['status'] === 'Aktif')) ?></div>
        <div class="text-[10px] text-slate-500">Koneksi Lintas Jawa & Sumatera</div>
    </div>
    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Hub Transit</div>
        <div class="text-xl font-black text-amber-600 mt-0.5"><?= $stats['total_hub'] ?? 4 ?></div>
        <div class="text-[10px] text-slate-500">Sortir Terpusat</div>
    </div>
    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tarif Termurah</div>
        <div class="text-xl font-black text-slate-800 mt-0.5">Rp 3.800<span class="text-xs font-normal text-slate-400">/kg</span></div>
        <div class="text-[10px] text-emerald-600 font-medium">CRB → BDG (Tol Cisumdawu)</div>
    </div>
</div>

<!-- Main Split Grid: Left = Tabs & Tables, Right = Kalkulator Tarif (Desktop 2-Col) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    
    <!-- Left Column: Tabs for Wilayah Operasional & Rute & Tarif (Col 7) -->
    <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 shadow-sm p-4 sm:p-5">
        
        <!-- Tab Buttons -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
            <div class="flex gap-4">
                <button type="button" onclick="switchTab('tab-rute')" id="btn-tab-rute" class="text-sm font-bold pb-2 border-b-2 border-[#0d5c58] text-[#0d5c58] transition">
                    Rute & tarif
                </button>
                <button type="button" onclick="switchTab('tab-wilayah')" id="btn-tab-wilayah" class="text-sm font-bold pb-2 border-b-2 border-transparent text-slate-400 hover:text-slate-700 transition">
                    Wilayah operasional
                </button>
            </div>
            
            <!-- Live Search Bar -->
            <div class="relative w-44 sm:w-60">
                <input type="text" id="live-search-input" placeholder="Cari rute/kota..." class="w-full text-xs pl-8 pr-3 py-1.5 border border-slate-200 rounded-lg focus:outline-none focus:border-teal-700">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <!-- TAB CONTENT: RUTE & TARIF -->
        <div id="tab-rute" class="tab-pane">
            <div class="table-responsive">
                <table class="table" id="table-rute">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Asal → Tujuan</th>
                            <th>Tarif/kg</th>
                            <th>Estimasi</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="rute-tbody">
                        <?php foreach ($ruteList as $r): ?>
                        <tr data-search="<?= strtolower("{$r['kode_rute']} {$r['asal_nama']} {$r['tujuan_nama']} {$r['status']}") ?>">
                            <td class="font-mono font-bold text-xs text-teal-900"><?= e($r['kode_rute']) ?></td>
                            <td>
                                <div class="font-bold text-xs"><?= e($r['asal_nama']) ?> → <?= e($r['tujuan_nama']) ?></div>
                                <div class="text-[11px] text-slate-400"><?= e($r['catatan'] ?: 'Kargo Darat Antar-Kota') ?></div>
                            </td>
                            <td class="font-semibold text-xs"><?= format_rupiah($r['tarif_per_kg']) ?></td>
                            <td class="text-xs"><?= e($r['estimasi_hari']) ?></td>
                            <td>
                                <?php if ($r['status'] === 'Aktif'): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php elseif ($r['status'] === 'Jadwal terbatas'): ?>
                                    <span class="badge badge-warning">Terbatas</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <button type="button" onclick="editRute(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)" class="text-xs text-teal-700 hover:text-teal-900 font-semibold mr-2">
                                    Ubah
                                </button>
                                <button type="button" onclick="deleteRute(<?= $r['id'] ?>, '<?= e($r['kode_rute']) ?>')" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span id="rute-count-info">Menampilkan <?= count($ruteList) ?> rute · Wilayah: <?= count($wilayahList) ?> kota, <?= $stats['total_hub'] ?? 3 ?> hub</span>
            </div>
        </div>

        <!-- TAB CONTENT: WILAYAH OPERASIONAL -->
        <div id="tab-wilayah" class="tab-pane hidden">
            <div class="table-responsive">
                <table class="table" id="table-wilayah">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Kota & Gudang</th>
                            <th>Provinsi</th>
                            <th>Tipe Titik</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="wilayah-tbody">
                        <?php foreach ($wilayahList as $w): ?>
                        <tr data-search="<?= strtolower("{$w['kode_kota']} {$w['nama_kota']} {$w['provinsi']} {$w['tipe']}") ?>">
                            <td class="font-mono font-bold text-xs text-amber-700"><?= e($w['kode_kota']) ?></td>
                            <td>
                                <div class="font-bold text-xs"><?= e($w['nama_kota']) ?></div>
                                <div class="text-[11px] text-slate-400 truncate max-w-xs"><?= e($w['alamat_gudang'] ?: '-') ?></div>
                            </td>
                            <td class="text-xs"><?= e($w['provinsi']) ?></td>
                            <td>
                                <span class="badge <?= $w['tipe'] === 'Cabang Utama' ? 'badge-info' : ($w['tipe'] === 'Hub Transit' ? 'badge-warning' : 'badge-neutral') ?>">
                                    <?= e($w['tipe']) ?>
                                </span>
                            </td>
                            <td class="text-xs font-mono"><?= e($w['telepon'] ?: '-') ?></td>
                            <td>
                                <span class="badge <?= $w['status'] === 'Aktif' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= e($w['status']) ?>
                                </span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <button type="button" onclick="editWilayah(<?= htmlspecialchars(json_encode($w), ENT_QUOTES, 'UTF-8') ?>)" class="text-xs text-teal-700 hover:text-teal-900 font-semibold mr-2">
                                    Ubah
                                </button>
                                <button type="button" onclick="deleteWilayah(<?= $w['id'] ?>, '<?= e($w['nama_kota']) ?>')" class="text-xs text-rose-600 hover:text-rose-800 font-semibold">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Total Jaringan Operasional: <?= count($wilayahList) ?> titik layanan logistik.
            </div>
        </div>

    </div>

    <!-- Right Column: Kalkulator Tarif Live JS (Col 5) -->
    <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 shadow-sm p-4 sm:p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-900">Kalkulator tarif</h3>
            <span class="text-[11px] font-semibold text-teal-800 bg-teal-50 px-2.5 py-1 rounded-full border border-teal-200">
                Hitung Otomatis (JS)
            </span>
        </div>

        <form id="calc-form" class="space-y-4">
            <!-- Rute Dropdown -->
            <div class="form-group mb-3">
                <label class="form-label" for="calc-rute">Rute</label>
                <select id="calc-rute" name="rute_id" class="form-select text-xs">
                    <?php foreach ($ruteList as $r): ?>
                        <option value="<?= $r['id'] ?>" 
                                data-tarif="<?= $r['tarif_per_kg'] ?>"
                                data-min-berat="<?= $r['min_berat_kg'] ?>"
                                data-asal="<?= e($r['asal_nama']) ?>"
                                data-tujuan="<?= e($r['tujuan_nama']) ?>"
                                data-kode="<?= e($r['kode_rute']) ?>"
                                <?= $r['kode_rute'] === 'CRB-JKT' ? 'selected' : '' ?>>
                            <?= e($r['asal_nama']) ?> → <?= e($r['tujuan_nama']) ?> (<?= format_rupiah($r['tarif_per_kg']) ?>/kg)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dimensi 3 Kolom -->
            <div class="grid grid-cols-3 gap-2">
                <div class="form-group mb-2">
                    <label class="form-label text-xs" for="calc-panjang">Panjang cm</label>
                    <input type="number" id="calc-panjang" name="panjang" value="60" min="0" step="1" class="form-input text-xs">
                </div>
                <div class="form-group mb-2">
                    <label class="form-label text-xs" for="calc-lebar">Lebar cm</label>
                    <input type="number" id="calc-lebar" name="lebar" value="40" min="0" step="1" class="form-input text-xs">
                </div>
                <div class="form-group mb-2">
                    <label class="form-label text-xs" for="calc-tinggi">Tinggi cm</label>
                    <input type="number" id="calc-tinggi" name="tinggi" value="50" min="0" step="1" class="form-input text-xs">
                </div>
            </div>

            <!-- Berat Aktual & Koli -->
            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-3">
                    <label class="form-label text-xs" for="calc-berat">Berat aktual kg</label>
                    <input type="number" id="calc-berat" name="berat_aktual" value="14" min="0.1" step="0.1" class="form-input text-xs">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label text-xs" for="calc-koli">Jumlah koli</label>
                    <input type="number" id="calc-koli" name="jumlah_koli" value="2" min="1" step="1" class="form-input text-xs">
                </div>
            </div>

            <!-- Pilihan Layanan -->
            <div class="form-group mb-3">
                <label class="form-label text-xs">Jenis Layanan</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="border border-slate-200 rounded-lg p-2 text-center cursor-pointer text-xs has-[:checked]:border-teal-700 has-[:checked]:bg-teal-50">
                        <input type="radio" name="layanan" value="Reguler" checked class="hidden calc-layanan-radio">
                        <div class="font-bold text-slate-800">Reguler</div>
                        <div class="text-[10px] text-slate-500">1-2 Hari</div>
                    </label>
                    <label class="border border-slate-200 rounded-lg p-2 text-center cursor-pointer text-xs has-[:checked]:border-teal-700 has-[:checked]:bg-teal-50">
                        <input type="radio" name="layanan" value="Express Kargo" class="hidden calc-layanan-radio">
                        <div class="font-bold text-slate-800">Express</div>
                        <div class="text-[10px] text-slate-500">Overnight</div>
                    </label>
                    <label class="border border-slate-200 rounded-lg p-2 text-center cursor-pointer text-xs has-[:checked]:border-teal-700 has-[:checked]:bg-teal-50">
                        <input type="radio" name="layanan" value="Trucking FTL" class="hidden calc-layanan-radio">
                        <div class="font-bold text-slate-800">Trucking</div>
                        <div class="text-[10px] text-slate-500">Tonase</div>
                    </label>
                </div>
            </div>

            <!-- Calculation Box (Dark Teal Card - Matching Mockup!) -->
            <div class="calc-card my-4">
                <div class="calc-total-label">Total ongkir</div>
                <div class="calc-total-amount" id="calc-display-total">Rp 180.000</div>
                
                <div class="calc-detail-row">
                    <span>Berat volume (P×L×T ÷ 6000)</span>
                    <span id="calc-display-volume" class="font-mono font-semibold">40.0 kg</span>
                </div>
                <div class="calc-detail-row">
                    <span>Berat dikenakan (terbesar)</span>
                    <span id="calc-display-chargeable" class="font-mono font-semibold text-amber-400">40.0 kg (volume)</span>
                </div>
                <div class="calc-detail-row">
                    <span>Tarif dasar rute</span>
                    <span id="calc-display-rate" class="font-mono">Rp 4.500 / kg</span>
                </div>
            </div>

            <!-- CTA: Buat Resi Dari Tarif Ini -->
            <button type="button" id="btn-buat-resi-dari-tarif" class="w-full py-3 bg-[#0d5c58] hover:bg-[#094744] text-white font-bold rounded-lg transition shadow-md text-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Buat resi dari tarif ini</span>
            </button>
        </form>
    </div>

</div>

<!-- MODAL TAMBAH RUTE -->
<div id="modal-tambah-rute" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <div class="card-header">
            <h3 class="card-title text-base">Tambah Rute & Tarif Baru</h3>
            <button type="button" onclick="closeModal('modal-tambah-rute')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-tambah-rute" action="<?= base_url('rute/store') ?>" method="POST" class="p-5 space-y-4">
            <?= \Core\Security::csrfField() ?>
            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Kota Asal</label>
                    <select name="kota_asal_id" class="form-select text-xs" required>
                        <?php foreach ($wilayahList as $w): ?>
                            <option value="<?= $w['id'] ?>" <?= $w['kode_kota'] === 'CRB' ? 'selected' : '' ?>><?= e($w['nama_kota']) ?> (<?= e($w['kode_kota']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Kota Tujuan</label>
                    <select name="kota_tujuan_id" class="form-select text-xs" required>
                        <?php foreach ($wilayahList as $w): ?>
                            <option value="<?= $w['id'] ?>"><?= e($w['nama_kota']) ?> (<?= e($w['kode_kota']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Tarif per Kg (Rp)</label>
                    <input type="number" name="tarif_per_kg" placeholder="misal: 4500" required min="500" step="100" class="form-input text-xs">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Estimasi Waktu</label>
                    <input type="text" name="estimasi_hari" placeholder="misal: 1-2 hari" required class="form-input text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Status Rute</label>
                    <select name="status" class="form-select text-xs">
                        <option value="Aktif">Aktif</option>
                        <option value="Jadwal terbatas">Jadwal terbatas</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Min. Berat (kg)</label>
                    <input type="number" name="min_berat_kg" value="1.0" min="0.5" step="0.5" class="form-input text-xs">
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="form-label text-xs">Catatan Rute</label>
                <input type="text" name="catatan" placeholder="misal: Jalur tol express harian" class="form-input text-xs">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="closeModal('modal-tambah-rute')" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm bg-[#0d5c58]">Simpan Rute</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH WILAYAH -->
<div id="modal-tambah-wilayah" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <div class="card-header">
            <h3 class="card-title text-base">Tambah Titik Wilayah Operasional</h3>
            <button type="button" onclick="closeModal('modal-tambah-wilayah')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-tambah-wilayah" action="<?= base_url('wilayah/store') ?>" method="POST" class="p-5 space-y-4">
            <?= \Core\Security::csrfField() ?>
            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Kode Kota (3 Huruf)</label>
                    <input type="text" name="kode_kota" placeholder="misal: TGR" maxlength="5" required class="form-input text-xs font-mono uppercase">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Nama Kota</label>
                    <input type="text" name="nama_kota" placeholder="misal: Tangerang" required class="form-input text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Provinsi</label>
                    <input type="text" name="provinsi" placeholder="misal: Banten" required class="form-input text-xs">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Tipe Jaringan</label>
                    <select name="tipe" class="form-select text-xs">
                        <option value="Drop Point">Drop Point</option>
                        <option value="Hub Transit">Hub Transit</option>
                        <option value="Cabang Utama">Cabang Utama</option>
                    </select>
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="form-label text-xs">Alamat Gudang / Titik Operasional</label>
                <textarea name="alamat_gudang" rows="2" placeholder="Alamat detail gudang transit..." class="form-textarea text-xs"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Telepon Gudang</label>
                    <input type="text" name="telepon" placeholder="021-..." class="form-input text-xs">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Status</label>
                    <select name="status" class="form-select text-xs">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="closeModal('modal-tambah-wilayah')" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm bg-[#0d5c58]">Simpan Wilayah</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT RUTE -->
<div id="modal-edit-rute" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <div class="card-header">
            <h3 class="card-title text-base">Ubah Data Rute & Tarif</h3>
            <button type="button" onclick="closeModal('modal-edit-rute')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-edit-rute" action="<?= base_url('rute/update') ?>" method="POST" class="p-5 space-y-4">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="id" id="edit-rute-id">

            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs">
                <div class="font-bold text-slate-800" id="edit-rute-nama">Cirebon → Jakarta</div>
                <div class="text-slate-500 font-mono" id="edit-rute-kode">CRB-JKT</div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Tarif per Kg (Rp)</label>
                    <input type="number" id="edit-rute-tarif" name="tarif_per_kg" required min="500" step="100" class="form-input text-xs">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Estimasi Waktu</label>
                    <input type="text" id="edit-rute-estimasi" name="estimasi_hari" required class="form-input text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Status Rute</label>
                    <select id="edit-rute-status" name="status" class="form-select text-xs">
                        <option value="Aktif">Aktif</option>
                        <option value="Jadwal terbatas">Jadwal terbatas</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Min. Berat (kg)</label>
                    <input type="number" id="edit-rute-min-berat" name="min_berat_kg" min="0.5" step="0.5" class="form-input text-xs">
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="form-label text-xs">Catatan Rute</label>
                <input type="text" id="edit-rute-catatan" name="catatan" class="form-input text-xs">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="closeModal('modal-edit-rute')" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm bg-[#0d5c58]">Perbarui Rute</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT WILAYAH -->
<div id="modal-edit-wilayah" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <div class="card-header">
            <h3 class="card-title text-base">Ubah Data Wilayah Operasional</h3>
            <button type="button" onclick="closeModal('modal-edit-wilayah')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="form-edit-wilayah" action="<?= base_url('wilayah/update') ?>" method="POST" class="p-5 space-y-4">
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="id" id="edit-wilayah-id">

            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs flex items-center justify-between">
                <div>
                    <span class="text-slate-400">Kode Kota (Tetap):</span>
                    <span class="font-mono font-bold text-amber-700 ml-1 text-sm" id="edit-wilayah-kode">CRB</span>
                </div>
                <span class="text-[11px] text-slate-400">Kode operasional resmi</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Nama Kota</label>
                    <input type="text" id="edit-wilayah-nama" name="nama_kota" required class="form-input text-xs">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Provinsi</label>
                    <input type="text" id="edit-wilayah-provinsi" name="provinsi" required class="form-input text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Tipe Jaringan</label>
                    <select id="edit-wilayah-tipe" name="tipe" class="form-select text-xs">
                        <option value="Drop Point">Drop Point</option>
                        <option value="Hub Transit">Hub Transit</option>
                        <option value="Cabang Utama">Cabang Utama</option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Status</label>
                    <select id="edit-wilayah-status" name="status" class="form-select text-xs">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="form-label text-xs">Alamat Gudang / Titik Operasional</label>
                <textarea id="edit-wilayah-alamat" name="alamat_gudang" rows="2" class="form-textarea text-xs"></textarea>
            </div>

            <div class="form-group mb-0">
                <label class="form-label text-xs">Telepon Gudang</label>
                <input type="text" id="edit-wilayah-telepon" name="telepon" class="form-input text-xs">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="closeModal('modal-edit-wilayah')" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm bg-[#0d5c58]">Perbarui Wilayah</button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.getElementById(tabId).classList.remove('hidden');

        const btnRute = document.getElementById('btn-tab-rute');
        const btnWilayah = document.getElementById('btn-tab-wilayah');

        if (tabId === 'tab-rute') {
            btnRute.className = "text-sm font-bold pb-2 border-b-2 border-[#0d5c58] text-[#0d5c58] transition";
            btnWilayah.className = "text-sm font-bold pb-2 border-b-2 border-transparent text-slate-400 hover:text-slate-700 transition";
        } else {
            btnWilayah.className = "text-sm font-bold pb-2 border-b-2 border-[#0d5c58] text-[#0d5c58] transition";
            btnRute.className = "text-sm font-bold pb-2 border-b-2 border-transparent text-slate-400 hover:text-slate-700 transition";
        }
    }

    function editRute(rute) {
        document.getElementById('edit-rute-id').value = rute.id;
        document.getElementById('edit-rute-nama').textContent = (rute.asal_nama || 'Cirebon') + ' → ' + (rute.tujuan_nama || 'Jakarta');
        document.getElementById('edit-rute-kode').textContent = rute.kode_rute;
        document.getElementById('edit-rute-tarif').value = parseFloat(rute.tarif_per_kg);
        document.getElementById('edit-rute-estimasi').value = rute.estimasi_hari;
        document.getElementById('edit-rute-status').value = rute.status;
        document.getElementById('edit-rute-min-berat').value = parseFloat(rute.min_berat_kg || 1);
        document.getElementById('edit-rute-catatan').value = rute.catatan || '';
        openModal('modal-edit-rute');
    }

    function editWilayah(w) {
        document.getElementById('edit-wilayah-id').value = w.id;
        document.getElementById('edit-wilayah-kode').textContent = w.kode_kota;
        document.getElementById('edit-wilayah-nama').value = w.nama_kota;
        document.getElementById('edit-wilayah-provinsi').value = w.provinsi;
        document.getElementById('edit-wilayah-tipe').value = w.tipe;
        document.getElementById('edit-wilayah-alamat').value = w.alamat_gudang || '';
        document.getElementById('edit-wilayah-telepon').value = w.telepon || '';
        document.getElementById('edit-wilayah-status').value = w.status;
        openModal('modal-edit-wilayah');
    }

    function deleteRute(id, kode) {
        if (confirm('Apakah Anda yakin ingin menghapus rute ' + kode + '?')) {
            const formData = new FormData();
            formData.append('id', id);
            fetch(window.BASE_URL + '/rute/delete', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus rute');
                }
            });
        }
    }

    function deleteWilayah(id, nama) {
        if (confirm('Apakah Anda yakin ingin menghapus wilayah ' + nama + '? Semua rute terkait juga akan terhapus.')) {
            const formData = new FormData();
            formData.append('id', id);
            fetch(window.BASE_URL + '/wilayah/delete', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus wilayah');
                }
            });
        }
    }
</script>
