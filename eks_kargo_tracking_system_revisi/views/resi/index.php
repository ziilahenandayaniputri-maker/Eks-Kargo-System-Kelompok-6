<?php
// views/resi/index.php
$p = $prefill ?? [];
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Penerbitan resi</h2>
        <p class="text-xs sm:text-sm text-slate-500">Isi data paket, cek validasi, lalu cetak</p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="openModal('modal-daftar-resi')" class="btn btn-outline btn-sm shadow-sm">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
            <span>Daftar resi</span>
        </button>
    </div>
</div>

<!-- Two-Column Layout (Form on Left, Live Receipt Preview on Right) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    
    <!-- LEFT COLUMN: FORMULIR PENERBITAN RESI (Col 7) -->
    <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <form id="form-resi" action="<?= base_url('resi/store') ?>" method="POST" novalidate>
            <?= \Core\Security::csrfField() ?>
            <input type="hidden" name="action_type" id="resi-action-type" value="terbitkan">

            <!-- SECTION 1: DATA PENGIRIM & PENERIMA -->
            <div class="mb-6">
                <h3 class="text-sm font-bold text-slate-900 mb-3 pb-1 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                    <span>Data pengirim & penerima</span>
                </h3>

                <!-- Nama Pengirim & Telepon Pengirim -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="nama_pengirim">Nama pengirim</label>
                        <input type="text" id="nama_pengirim" name="nama_pengirim" value="<?= e($p['nama_pengirim'] ?? 'CV Mitra Toni Jaya') ?>" required class="form-input text-xs" placeholder="Nama pengirim / perusahaan">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="telp_pengirim">Telepon pengirim</label>
                        <input type="text" id="telp_pengirim" name="telp_pengirim" value="<?= e($p['telp_pengirim'] ?? '0231 480 221') ?>" required class="form-input text-xs" placeholder="0231... atau 08...">
                    </div>
                </div>

                <!-- Nama Penerima & Telepon Penerima (With Live Validation) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="nama_penerima">Nama penerima</label>
                        <input type="text" id="nama_penerima" name="nama_penerima" value="<?= e($p['nama_penerima'] ?? 'Dewi Lestari') ?>" required class="form-input text-xs" placeholder="Nama lengkap penerima">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="telp_penerima">Telepon penerima</label>
                        <input type="text" id="telp_penerima" name="telp_penerima" value="<?= e($p['telp_penerima'] ?? '0812-3489-9912') ?>" required class="form-input text-xs" placeholder="08xxxxxxxxxx">
                        <!-- Red Inline Warning for Phone -->
                        <span id="telp-penerima-error" class="invalid-feedback hidden">Nomor tidak lengkap. Isi 10-13 digit, mulai 08.</span>
                    </div>
                </div>

                <!-- Alamat Penerima -->
                <div class="form-group mb-3">
                    <label class="form-label text-xs" for="alamat_penerima">Alamat penerima</label>
                    <textarea id="alamat_penerima" name="alamat_penerima" rows="2" required class="form-textarea text-xs" placeholder="Alamat lengkap tujuan pengantaran..."><?= e($p['alamat_penerima'] ?? 'Jl. Cendana Raya No. 12, Kebayoran Baru, Jakarta Selatan') ?></textarea>
                </div>

                <!-- Rute & Kode Pos (With Live Validation) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="resi_rute_id">Rute</label>
                        <select id="resi_rute_id" name="rute_id" class="form-select text-xs" required>
                            <?php foreach ($ruteList as $r): ?>
                                <option value="<?= $r['id'] ?>" 
                                        data-tarif="<?= $r['tarif_per_kg'] ?>"
                                        data-min-berat="<?= $r['min_berat_kg'] ?>"
                                        data-asal-kode="<?= e($r['asal_kode']) ?>"
                                        data-tujuan-kode="<?= e($r['tujuan_kode']) ?>"
                                        data-asal-nama="<?= e($r['asal_nama']) ?>"
                                        data-tujuan-nama="<?= e($r['tujuan_nama']) ?>"
                                        <?= ($r['id'] == ($p['rute_id'] ?? 1)) ? 'selected' : '' ?>>
                                    <?= e($r['asal_nama']) ?> → <?= e($r['tujuan_nama']) ?> (<?= format_rupiah($r['tarif_per_kg']) ?>/kg)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="kode_pos_penerima">Kode pos</label>
                        <input type="text" id="kode_pos_penerima" name="kode_pos_penerima" value="<?= e($p['kode_pos_penerima'] ?? '12150') ?>" maxlength="5" required class="form-input text-xs font-mono" placeholder="5 digit angka">
                        <!-- Red Inline Warning for Postal Code -->
                        <span id="kode-pos-error" class="invalid-feedback hidden">Kode pos harus 5 digit.</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: PAKET KIRIMAN -->
            <div class="mb-6">
                <h3 class="text-sm font-bold text-slate-900 mb-3 pb-1 border-b border-slate-100 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Paket kiriman</span>
                </h3>

                <!-- Isi Paket & Nilai Barang -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="isi_paket">Isi paket</label>
                        <input type="text" id="isi_paket" name="isi_paket" value="<?= e($p['isi_paket'] ?? 'Pupuk organik, 2 koli') ?>" required class="form-input text-xs" placeholder="Deskripsi barang kargo">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="nilai_barang">Nilai barang (Rp)</label>
                        <input type="number" id="nilai_barang" name="nilai_barang" value="<?= e($p['nilai_barang'] ?? 3500000) ?>" min="0" step="10000" class="form-input text-xs font-mono" placeholder="Estimasi harga barang">
                    </div>
                </div>

                <!-- Dimensi 3 Kolom & Berat & Koli -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mb-3">
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="resi_panjang">Panjang cm</label>
                        <input type="number" id="resi_panjang" name="panjang" value="<?= e($p['panjang'] ?? 60) ?>" min="0" class="form-input text-xs">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="resi_lebar">Lebar cm</label>
                        <input type="number" id="resi_lebar" name="lebar" value="<?= e($p['lebar'] ?? 40) ?>" min="0" class="form-input text-xs">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="resi_tinggi">Tinggi cm</label>
                        <input type="number" id="resi_tinggi" name="tinggi" value="<?= e($p['tinggi'] ?? 50) ?>" min="0" class="form-input text-xs">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label text-xs" for="resi_berat">Berat kg</label>
                        <input type="number" id="resi_berat" name="berat_aktual" value="<?= e($p['berat_aktual'] ?? 14) ?>" min="0.1" step="0.1" class="form-input text-xs">
                    </div>
                    <div class="form-group mb-0 col-span-2 sm:col-span-1">
                        <label class="form-label text-xs" for="resi_koli">Jml Koli</label>
                        <input type="number" id="resi_koli" name="jumlah_koli" value="<?= e($p['jumlah_koli'] ?? 2) ?>" min="1" class="form-input text-xs">
                    </div>
                </div>

                <!-- Layanan -->
                <div class="form-group mb-4">
                    <label class="form-label text-xs" for="resi_layanan">Layanan pengiriman</label>
                    <select id="resi_layanan" name="layanan" class="form-select text-xs">
                        <option value="Reguler" <?= ($p['layanan'] ?? '') === 'Reguler' ? 'selected' : '' ?>>Reguler (Kargo Darat Antar-Kota)</option>
                        <option value="Express Kargo" <?= ($p['layanan'] ?? '') === 'Express Kargo' ? 'selected' : '' ?>>Express Kargo (Overnight Priority)</option>
                        <option value="Trucking FTL" <?= ($p['layanan'] ?? '') === 'Trucking FTL' ? 'selected' : '' ?>>Trucking FTL (Dedicated Charter)</option>
                    </select>
                </div>

                <!-- Form Action Buttons -->
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" onclick="document.getElementById('resi-action-type').value='terbitkan'" class="btn btn-primary bg-[#0d5c58] hover:bg-[#094744] text-xs sm:text-sm py-2.5 px-5 font-bold shadow-md">
                        Terbitkan resi
                    </button>
                    <button type="submit" onclick="document.getElementById('resi-action-type').value='draf'" class="btn btn-outline text-xs sm:text-sm py-2.5 px-4 font-semibold">
                        Simpan draf
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- RIGHT COLUMN: LIVE RECEIPT PREVIEW (Col 5) -->
    <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <!-- Tab Selector: Thermal 58 mm | A4 -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
            <div class="flex gap-4">
                <button type="button" id="btn-preview-thermal" onclick="setReceiptFormat('thermal')" class="text-xs sm:text-sm font-bold pb-2 border-b-2 border-[#0d5c58] text-[#0d5c58] transition">
                    Thermal 58 mm
                </button>
                <button type="button" id="btn-preview-a4" onclick="setReceiptFormat('a4')" class="text-xs sm:text-sm font-bold pb-2 border-b-2 border-transparent text-slate-400 hover:text-slate-700 transition">
                    A4
                </button>
            </div>
            <span class="text-[10px] text-slate-400 font-medium">Pratinjau Langsung</span>
        </div>

        <!-- Thermal Receipt Preview Paper (Matching Screenshot 2 & Mockup) -->
        <div id="receipt-preview-container" class="receipt-preview-box">
            <div class="text-center font-black text-base tracking-widest text-slate-900">KARGOTRACK</div>
            <div class="text-center text-[11px] text-slate-500 mb-2">Cabang Cirebon</div>
            
            <div class="border-t border-dashed border-slate-300 my-2"></div>
            
            <!-- Dynamic Barcode Representation -->
            <div class="text-center py-1">
                <svg id="preview-barcode-svg" class="barcode-svg mx-auto"></svg>
                <div id="preview-no-resi" class="font-mono text-xs font-bold text-slate-800 mt-1">KT-CRB-261030-0047</div>
            </div>
            
            <div class="border-t border-dashed border-slate-300 my-2"></div>
            
            <div class="text-xs space-y-1 text-slate-700">
                <div class="flex justify-between">
                    <span class="text-slate-400">Tgl</span>
                    <span id="preview-tgl" class="font-medium"><?= date('d/m/Y H:i') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Rute</span>
                    <span id="preview-rute" class="font-bold text-teal-900">CRB → JKT</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Layanan</span>
                    <span id="preview-layanan" class="font-medium">Reguler</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Berat</span>
                    <span id="preview-berat" class="font-medium">14 kg / 2 koli</span>
                </div>
            </div>
            
            <div class="border-t border-dashed border-slate-300 my-2"></div>
            
            <div class="text-xs">
                <div class="text-slate-400 text-[10px] uppercase font-bold">Penerima</div>
                <div id="preview-penerima-nama" class="font-bold text-slate-800">Dewi Lestari</div>
                <div id="preview-penerima-alamat" class="text-slate-500 text-[11px] leading-tight mt-0.5">
                    Jl. Cendana Raya No. 12, Kebayoran Baru, Jakarta
                </div>
            </div>
            
            <div class="border-t border-dashed border-slate-300 my-2"></div>
            
            <div class="flex justify-between items-center text-xs">
                <span class="font-bold text-slate-800">Ongkir</span>
                <span id="preview-ongkir" class="font-bold text-sm text-slate-900">Rp 180.000</span>
            </div>
            
            <div class="border-t border-dashed border-slate-300 my-2"></div>
            
            <div id="preview-lacak-text" class="text-[10px] text-center text-slate-400 font-mono">
                Lacak: kargotrack.id/KT-CRB-261030-0047
            </div>
        </div>

        <!-- Big CTA Button: Cetak Resi (Gold/Orange matching screenshot 2) -->
        <button type="button" id="btn-cetak-resi" onclick="triggerPrintCurrentReceipt()" class="w-full mt-4 py-3 bg-[#f59e0b] hover:bg-[#d97706] text-white font-bold rounded-lg transition shadow-md text-sm flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>Cetak resi</span>
        </button>
    </div>

</div>

<!-- MODAL DAFTAR RESI -->
<div id="modal-daftar-resi" class="modal-backdrop" style="display: none;">
    <div class="modal-content max-w-4xl">
        <div class="card-header">
            <div>
                <h3 class="card-title text-base">Daftar Resi Logistik</h3>
                <p class="text-xs text-slate-400">Riwayat resi penerbitan kargo cabang Cirebon</p>
            </div>
            <button type="button" onclick="closeModal('modal-daftar-resi')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <div class="p-4">
            <!-- Search Resi -->
            <div class="mb-4 relative">
                <input type="text" id="modal-search-resi" placeholder="Cari nomor resi, pengirim, penerima, atau barang..." class="form-input text-xs pl-8">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <!-- Resi Table Container -->
            <div class="table-responsive max-h-96 overflow-y-auto">
                <table class="table text-xs" id="table-daftar-resi">
                    <thead>
                        <tr>
                            <th>No. Resi</th>
                            <th>Pengirim & Penerima</th>
                            <th>Rute</th>
                            <th>Barang & Berat</th>
                            <th>Ongkir</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-daftar-resi">
                        <!-- Loaded dynamically via fetch / JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let currentReceiptFormat = 'thermal';
    let currentActiveResiNo = 'KT-CRB-261030-0047';
    let currentActiveResiId = 1;

    function setReceiptFormat(format) {
        currentReceiptFormat = format;
        const btnThermal = document.getElementById('btn-preview-thermal');
        const btnA4 = document.getElementById('btn-preview-a4');
        const container = document.getElementById('receipt-preview-container');

        if (format === 'thermal') {
            btnThermal.className = "text-xs sm:text-sm font-bold pb-2 border-b-2 border-[#0d5c58] text-[#0d5c58] transition";
            btnA4.className = "text-xs sm:text-sm font-bold pb-2 border-b-2 border-transparent text-slate-400 hover:text-slate-700 transition";
            container.className = "receipt-preview-box";
        } else {
            btnA4.className = "text-xs sm:text-sm font-bold pb-2 border-b-2 border-[#0d5c58] text-[#0d5c58] transition";
            btnThermal.className = "text-xs sm:text-sm font-bold pb-2 border-b-2 border-transparent text-slate-400 hover:text-slate-700 transition";
            container.className = "receipt-preview-box max-w-full";
        }
    }

    function triggerPrintCurrentReceipt() {
        if (currentActiveResiId) {
            window.open(window.BASE_URL + '/resi/printReceipt/' + currentActiveResiId + '/' + currentReceiptFormat, '_blank');
        } else {
            window.print();
        }
    }
</script>
