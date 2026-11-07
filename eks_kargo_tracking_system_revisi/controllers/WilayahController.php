<?php
namespace Controllers;

use Core\Controller;
use Models\Wilayah;
use Models\Rute;
use Models\Resi;

/**
 * Controller Master Wilayah Operasional, Rute & Kalkulator Tarif
 */
class WilayahController extends Controller
{
    private Wilayah $wilayahModel;
    private Rute $ruteModel;

    public function __construct()
    {
        $this->wilayahModel = new Wilayah();
        $this->ruteModel = new Rute();
    }

    /**
     * Halaman Utama Wilayah, Rute & Tarif
     */
    public function index(): void
    {
        $user = $this->requireAuth();

        $wilayahList = $this->wilayahModel->all('*', 'nama_kota ASC');
        $ruteList = $this->ruteModel->getRuteWithWilayah('r.kode_rute ASC');
        $stats = $this->wilayahModel->getSummaryStats();

        $this->view('wilayah/index', [
            'user' => $user,
            'wilayahList' => $wilayahList,
            'ruteList' => $ruteList,
            'stats' => $stats,
            'pageTitle' => 'Wilayah, Rute & Tarif - KargoTrack'
        ]);
    }

    /**
     * Simpan Wilayah Baru (AJAX / POST)
     */
    public function store(): void
    {
        $this->requireAuth();
        // BUG-01: Verifikasi CSRF
        if (!$this->verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'Token keamanan tidak valid.'], 403);
        }

        $data = [
            'kode_kota' => strtoupper(trim($_POST['kode_kota'] ?? '')),
            'nama_kota' => trim($_POST['nama_kota'] ?? ''),
            'provinsi' => trim($_POST['provinsi'] ?? ''),
            'tipe' => $_POST['tipe'] ?? 'Drop Point',
            'alamat_gudang' => trim($_POST['alamat_gudang'] ?? ''),
            'telepon' => trim($_POST['telepon'] ?? ''),
            'status' => $_POST['status'] ?? 'Aktif'
        ];

        $errors = $this->validate($data, [
            'kode_kota' => 'required',
            'nama_kota' => 'required',
            'provinsi' => 'required'
        ]);

        // Cek duplikasi kode kota
        if (empty($errors['kode_kota'])) {
            $existing = $this->wilayahModel->firstWhere(['kode_kota' => $data['kode_kota']]);
            if ($existing) {
                $errors['kode_kota'] = 'Kode kota ' . $data['kode_kota'] . ' sudah terdaftar.';
            }
        }

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors], 422);
            }
            $this->setFlash('error', 'Validasi gagal: ' . implode(', ', $errors));
            $this->redirect('wilayah');
        }

        $id = $this->wilayahModel->create($data);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "Wilayah {$data['nama_kota']} ({$data['kode_kota']}) berhasil ditambahkan.",
                'data' => array_merge(['id' => $id], $data)
            ]);
        }

        $this->setFlash('success', "Wilayah {$data['nama_kota']} berhasil ditambahkan.");
        $this->redirect('wilayah');
    }

    /**
     * Update Data Wilayah
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
            'nama_kota' => trim($_POST['nama_kota'] ?? ''),
            'provinsi' => trim($_POST['provinsi'] ?? ''),
            'tipe' => $_POST['tipe'] ?? 'Drop Point',
            'alamat_gudang' => trim($_POST['alamat_gudang'] ?? ''),
            'telepon' => trim($_POST['telepon'] ?? ''),
            'status' => $_POST['status'] ?? 'Aktif'
        ];

        $errors = $this->validate($data, [
            'nama_kota' => 'required',
            'provinsi' => 'required'
        ]);

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors], 422);
            }
            $this->setFlash('error', 'Validasi gagal: ' . implode(', ', $errors));
            $this->redirect('wilayah');
        }

        $this->wilayahModel->update($id, $data);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "Data wilayah {$data['nama_kota']} berhasil diperbarui."
            ]);
        }

        $this->setFlash('success', "Data wilayah berhasil diperbarui.");
        $this->redirect('wilayah');
    }

    /**
     * Hapus Wilayah
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
            $this->json(['success' => false, 'message' => 'ID wilayah tidak valid.'], 400);
        }

        $this->wilayahModel->delete($id);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Wilayah berhasil dihapus.']);
        }

        $this->setFlash('success', 'Wilayah berhasil dihapus.');
        $this->redirect('wilayah');
    }

    /**
     * Live Search Wilayah (JSON)
     */
    public function search(): void
    {
        $q = trim($_GET['q'] ?? '');
        $results = !empty($q) ? $this->wilayahModel->search($q) : $this->wilayahModel->all('*', 'nama_kota ASC');
        $this->json(['success' => true, 'data' => $results]);
    }
}

