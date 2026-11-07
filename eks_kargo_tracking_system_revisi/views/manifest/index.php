<?php
// views/manifest/index.php
?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Manifest Muatan Kargo</h2>
        <p class="text-xs sm:text-sm text-slate-500">Kelompokkan resi kiriman ke dalam armada truk logistik antar-kota</p>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="openModal('modal-tambah-manifest')" class="btn btn-primary btn-sm bg-[#0d5c58] hover:bg-[#094744]">
            + Susun Manifest Baru
        </button>
    </div>
</div>

<!-- Manifest Table Card -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
    <div class="card-header">
        <h3 class="card-title text-base">Daftar Manifest Armada Truk</h3>
        <span class="text-xs text-slate-400">Total <?= count($manifests) ?> armada muatan</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Manifest</th>
                    <th>Armada & Sopir</th>
                    <th>Rute Jalur</th>
                    <th>Keberangkatan</th>
                    <th>Muatan Resi</th>
                    <th>Total Berat</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($manifests as $m): ?>
                <tr>
                    <td class="font-mono font-bold text-xs text-teal-900"><?= e($m['no_manifest']) ?></td>
                    <td>
                        <div class="font-bold text-xs"><?= e($m['no_polisi']) ?></div>
                        <div class="text-[11px] text-slate-400">Sopir: <?= e($m['nama_sopir']) ?> (<?= e($m['telp_sopir'] ?: '-') ?>)</div>
                    </td>
                    <td class="text-xs">
                        <span class="font-semibold text-slate-800"><?= e($m['asal_nama']) ?> &rarr; <?= e($m['tujuan_nama']) ?></span>
                        <div class="text-[10px] text-slate-400"><?= e($m['catatan'] ?: 'Jalur Reguler') ?></div>
                    </td>
                    <td class="text-xs font-mono">
                        <?= date('d/m/Y H:i', strtotime($m['tgl_keberangkatan'])) ?>
                    </td>
                    <td class="text-xs">
                        <span class="font-bold text-slate-800"><?= e($m['total_resi']) ?> resi</span>
                        <span class="text-slate-400">(<?= e($m['total_koli']) ?> koli)</span>
                    </td>
                    <td class="text-xs font-semibold text-teal-800">
                        <?= number_format((float)$m['total_berat_kg'], 1) ?> kg
                    </td>
                    <td>
                        <span class="badge <?= ($m['status'] === 'Dalam Perjalanan') ? 'badge-warning' : (($m['status'] === 'Selesai') ? 'badge-success' : 'badge-info') ?>">
                            <?= e($m['status']) ?>
                        </span>
                    </td>
                    <td class="text-right whitespace-nowrap">
                        <?php if ($m['status'] === 'Siap Berangkat' || $m['status'] === 'Draft'): ?>
                            <button type="button" onclick="berangkatkanManifest(<?= $m['id'] ?>, '<?= e($m['no_manifest']) ?>', '<?= e($m['no_polisi']) ?>')" class="btn btn-sm btn-primary bg-amber-500 hover:bg-amber-600 text-white text-xs py-1 px-3">
                                Berangkatkan Truk
                            </button>
                        <?php else: ?>
                            <span class="text-xs text-slate-400 font-medium">Dalam Perjalanan</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH MANIFEST -->
<div id="modal-tambah-manifest" class="modal-backdrop" style="display: none;">
    <div class="modal-content max-w-lg">
        <div class="card-header">
            <h3 class="card-title text-base">Susun Manifest Muatan Truk</h3>
            <button type="button" onclick="closeModal('modal-tambah-manifest')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form action="<?= base_url('manifest/store') ?>" method="POST" class="p-5 space-y-4">
            <?= \Core\Security::csrfField() ?>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">No. Manifest</label>
                    <input type="text" name="no_manifest" value="<?= e($nextManifestNo) ?>" readonly class="form-input text-xs font-mono font-bold bg-slate-100">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">No. Polisi Armada</label>
                    <input type="text" name="no_polisi" placeholder="misal: B 9127 KCD" required class="form-input text-xs uppercase font-mono">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Nama Sopir</label>
                    <input type="text" name="nama_sopir" placeholder="Nama sopir" required class="form-input text-xs">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Telp Sopir</label>
                    <input type="text" name="telp_sopir" placeholder="08..." class="form-input text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Kota Asal</label>
                    <select name="kota_asal_id" class="form-select text-xs">
                        <?php foreach ($wilayahList as $w): ?>
                            <option value="<?= $w['id'] ?>" <?= $w['kode_kota'] === 'CRB' ? 'selected' : '' ?>><?= e($w['nama_kota']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label text-xs">Kota Tujuan</label>
                    <select name="kota_tujuan_id" class="form-select text-xs">
                        <?php foreach ($wilayahList as $w): ?>
                            <option value="<?= $w['id'] ?>" <?= $w['kode_kota'] === 'JKT' ? 'selected' : '' ?>><?= e($w['nama_kota']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="form-label text-xs">Pilih Resi Muatan Gudang</label>
                <div class="max-h-36 overflow-y-auto border border-slate-200 rounded-lg p-2 space-y-1.5 text-xs">
                    <?php if (empty($unassignedResi)): ?>
                        <div class="text-slate-400 py-2 text-center text-xs">Semua resi aktif sudah terdaftar di manifest.</div>
                    <?php else: ?>
                        <?php foreach ($unassignedResi as $ur): ?>
                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer">
                                <input type="checkbox" name="resi_ids[]" value="<?= $ur['id'] ?>" class="rounded text-teal-700">
                                <span class="font-mono font-bold text-teal-900"><?= e($ur['no_resi']) ?></span>
                                <span class="text-slate-600 truncate"><?= e($ur['isi_paket']) ?> (<?= e($ur['berat_dikenakan']) ?> kg)</span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="form-label text-xs">Catatan Armada / Rute</label>
                <input type="text" name="catatan" placeholder="Truk Fuso Box CDD 8 Ton..." class="form-input text-xs">
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t">
                <button type="button" onclick="closeModal('modal-tambah-manifest')" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm bg-[#0d5c58]">Simpan Manifest</button>
            </div>
        </form>
    </div>
</div>

<script>
    function berangkatkanManifest(id, noManifest, noPolisi) {
        if (confirm('Konfirmasi keberangkatan truk ' + noPolisi + ' (Manifest ' + noManifest + ')?\n\nStatus seluruh resi muatan akan otomatis diperbarui ke "Dalam perjalanan" dan ditambahkan tracking checkpoint.')) {
            const formData = new FormData();
            formData.append('id', id);
            fetch(window.BASE_URL + '/manifest/berangkatkan', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Gagal memberangkatkan armada');
                }
            });
        }
    }
</script>

