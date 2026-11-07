<?php
namespace Controllers;

use Core\Controller;
use Models\Manifest;
use Models\Wilayah;
use Models\Resi;

/**
 * Controller Manifest Muatan Armada Truk Kargo Antar-Kota
 */
class ManifestController extends Controller
{
    private Manifest $manifestModel;
    private Wilayah $wilayahModel;
    private Resi $resiModel;

    public function __construct()
    {
        $this->manifestModel = new Manifest();
        $this->wilayahModel = new Wilayah();
        $this->resiModel = new Resi();
    }

    /**
     * Tampilan Daftar Manifest & Armada Muatan
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $manifests = $this->manifestModel->getAllWithDetails();
        $wilayahList = $this->wilayahModel->all('*', 'nama_kota ASC');

        // BUG-23 FIX: Filter resi yang siap muat — 'Resi diterbitkan' DAN 'Paket diterima gudang'
        // yang belum masuk manifest (manifest_id IS NULL)
        $unassignedResi = $this->resiModel->getUnassignedReadyResi();

        $this->view('manifest/index', [
            'user' => $user,
            'manifests' => $manifests,
            'wilayahList' => $wilayahList,
            'unassignedResi' => $unassignedResi,
            'nextManifestNo' => $this->manifestModel->generateNoManifest(),
            'pageTitle' => 'Manifest Muatan Kargo - KargoTrack'
        ]);
    }

    /**
     * Simpan Manifest Baru
     */
    public function store(): void
    {
        $user = $this->requireAuth();
        // BUG-01: Verifikasi CSRF
        if (!$this->verifyCsrf()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Token keamanan tidak valid.'], 403);
            }
            $this->setFlash('error', 'Token keamanan tidak valid.');
            $this->redirect('manifest');
        }

        $data = [
            'no_manifest' => trim($_POST['no_manifest'] ?? $this->manifestModel->generateNoManifest()),
            'no_polisi' => strtoupper(trim($_POST['no_polisi'] ?? '')),
            'nama_sopir' => trim($_POST['nama_sopir'] ?? ''),
            'telp_sopir' => trim($_POST['telp_sopir'] ?? ''),
            'kota_asal_id' => (int)($_POST['kota_asal_id'] ?? 1),
            'kota_tujuan_id' => (int)($_POST['kota_tujuan_id'] ?? 2),
            'tgl_keberangkatan' => trim($_POST['tgl_keberangkatan'] ?? date('Y-m-d H:i:s')),
            'estimasi_tiba' => trim($_POST['estimasi_tiba'] ?? date('Y-m-d H:i:s', strtotime('+18 hours'))),
            'status' => 'Siap Berangkat',
            'catatan' => trim($_POST['catatan'] ?? '')
        ];

        $errors = $this->validate($data, [
            'no_manifest' => 'required',
            'no_polisi' => 'required',
            'nama_sopir' => 'required'
        ]);

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors], 422);
            }
            $this->setFlash('error', 'Validasi formulir manifest gagal.');
            $this->redirect('manifest');
        }

        $id = $this->manifestModel->create($data);

        // Jika ada resi yang dipilih untuk dimasukkan ke manifest
        // BUG-12 FIX: Update juga status_pengiriman resi menjadi 'Dimuat ke manifest'
        // agar tidak muncul lagi di daftar unassigned
        if (!empty($_POST['resi_ids']) && is_array($_POST['resi_ids'])) {
            foreach ($_POST['resi_ids'] as $resiId) {
                $this->resiModel->update((int)$resiId, [
                    'manifest_id' => $id,
                    'status_pengiriman' => 'Dimuat ke manifest'
                ]);
            }
        }

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "Manifest {$data['no_manifest']} armada truk {$data['no_polisi']} berhasil dibuat!"
            ]);
        }

        $this->setFlash('success', "Manifest {$data['no_manifest']} berhasil dibuat.");
        $this->redirect('manifest');
    }

    /**
     * Berangkatkan Armada Truk (Update Checkpoint Otomatis ke Seluruh Resi Muatan)
     */
    public function berangkatkan(): void
    {
        $user = $this->requireAuth();
        // BUG-01: Verifikasi CSRF
        if (!$this->verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'Token keamanan tidak valid.'], 403);
        }
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID manifest tidak valid.'], 400);
        }

        $success = $this->manifestModel->berangkatkan($id, $user['nama'] ?? 'Admin Dispatch');

        if ($this->isAjax()) {
            $this->json([
                'success' => $success,
                'message' => 'Armada truk telah diberangkatkan! Status seluruh resi muatan diperbarui.'
            ]);
        }

        $this->setFlash('success', 'Armada truk telah diberangkatkan dan resi muatan telah terupdate.');
        $this->redirect('manifest');
    }
}

