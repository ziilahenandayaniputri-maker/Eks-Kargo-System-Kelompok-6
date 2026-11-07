<?php
namespace Controllers;

use Core\Controller;
use Models\Rute;
use Models\Wilayah;

/**
 * Controller Master Rute & Tarif
 */
class RuteController extends Controller
{
    private Rute $ruteModel;
    private Wilayah $wilayahModel;

    public function __construct()
    {
        $this->ruteModel = new Rute();
        $this->wilayahModel = new Wilayah();
    }

    /**
     * Simpan Rute Baru
     */
    public function store(): void
    {
        $this->requireAuth();
        // BUG-01: Verifikasi CSRF
        if (!$this->verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'Token keamanan tidak valid.'], 403);
        }

        $asalId = (int)($_POST['kota_asal_id'] ?? 0);
        $tujuanId = (int)($_POST['kota_tujuan_id'] ?? 0);

        $asal = $this->wilayahModel->find($asalId);
        $tujuan = $this->wilayahModel->find($tujuanId);

        if (!$asal || !$tujuan) {
            $this->json(['success' => false, 'message' => 'Kota asal atau tujuan tidak valid.'], 422);
        }

        $kodeRute = trim($_POST['kode_rute'] ?? '');
        if (empty($kodeRute)) {
            $kodeRute = $asal['kode_kota'] . '-' . $tujuan['kode_kota'];
        }

        $data = [
            'kode_rute' => strtoupper($kodeRute),
            'kota_asal_id' => $asalId,
            'kota_tujuan_id' => $tujuanId,
            'tarif_per_kg' => (float)($_POST['tarif_per_kg'] ?? 0),
            'min_berat_kg' => (float)($_POST['min_berat_kg'] ?? 1.0),
            'estimasi_hari' => trim($_POST['estimasi_hari'] ?? '1-2 hari'),
            'status' => $_POST['status'] ?? 'Aktif',
            'catatan' => trim($_POST['catatan'] ?? '')
        ];

        $errors = $this->validate($data, [
            'kode_rute' => 'required',
            'tarif_per_kg' => 'required|numeric|min:500',
            'estimasi_hari' => 'required'
        ]);

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors], 422);
            }
            $this->setFlash('error', 'Validasi gagal: ' . implode(', ', $errors));
            $this->redirect('wilayah');
        }

        $id = $this->ruteModel->create($data);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "Rute {$data['kode_rute']} ({$asal['nama_kota']} → {$tujuan['nama_kota']}) berhasil ditambahkan.",
                'data' => array_merge(['id' => $id], $data)
            ]);
        }

        $this->setFlash('success', "Rute {$data['kode_rute']} berhasil ditambahkan.");
        $this->redirect('wilayah');
    }

    /**
     * Update Rute
     */
    public function update(): void
    {
        $this->requireAuth();
        // BUG-01: Verifikasi CSRF
        if (!$this->verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'Token keamanan tidak valid.'], 403);
        }
        $id = (int)($_POST['id'] ?? 0);

        $data = [
            'tarif_per_kg' => (float)($_POST['tarif_per_kg'] ?? 0),
            'min_berat_kg' => (float)($_POST['min_berat_kg'] ?? 1.0),
            'estimasi_hari' => trim($_POST['estimasi_hari'] ?? '1-2 hari'),
            'status' => $_POST['status'] ?? 'Aktif',
            'catatan' => trim($_POST['catatan'] ?? '')
        ];

        $errors = $this->validate($data, [
            'tarif_per_kg' => 'required|numeric|min:500',
            'estimasi_hari' => 'required'
        ]);

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors], 422);
            }
            $this->setFlash('error', 'Validasi gagal.');
            $this->redirect('wilayah');
        }

        $this->ruteModel->update($id, $data);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Data rute dan tarif berhasil diperbarui.']);
        }

        $this->setFlash('success', 'Data rute dan tarif berhasil diperbarui.');
        $this->redirect('wilayah');
    }

    /**
     * Hapus Rute
     */
    public function delete(): void
    {
        $this->requireAuth();
        // BUG-01: Verifikasi CSRF
        if (!$this->verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'Token keamanan tidak valid.'], 403);
        }
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID rute tidak valid.'], 400);
        }

        $this->ruteModel->delete($id);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Rute berhasil dihapus.']);
        }

        $this->setFlash('success', 'Rute berhasil dihapus.');
        $this->redirect('wilayah');
    }

    /**
     * Live Search Rute (JSON)
     */
    public function search(): void
    {
        $q = trim($_GET['q'] ?? '');
        $results = !empty($q) ? $this->ruteModel->search($q) : $this->ruteModel->getRuteWithWilayah('r.kode_rute ASC');
        $this->json(['success' => true, 'data' => $results]);
    }
}

