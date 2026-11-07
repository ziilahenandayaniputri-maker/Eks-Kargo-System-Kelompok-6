<?php
namespace Controllers;

use Core\Controller;
use Models\Resi;
use Models\Tracking;
use Models\Manifest;

/**
 * Controller CRUD Checkpoint Tracking Perjalanan & Status Serah Terima (POD)
 */
class TrackingController extends Controller
{
    private Resi $resiModel;
    private Tracking $trackingModel;
    private Manifest $manifestModel;

    public function __construct()
    {
        $this->resiModel = new Resi();
        $this->trackingModel = new Tracking();
        $this->manifestModel = new Manifest();
    }

    /**
     * Halaman Dashboard Tracking & Serah Terima
     */
    public function index(?string $noResi = null): void
    {
        $user = $this->requireAuth();

        if (empty($noResi)) {
            // BUG-11 FIX (tracking): Jangan hardcode nomor resi; ambil resi terbaru
            $noResi = $_GET['resi'] ?? '';
        }

        $resi = null;
        if (!empty($noResi)) {
            $resi = $this->resiModel->getDetail($noResi);
        }
        if (!$resi) {
            // Ambil resi pertama dengan join rute jika nomor tidak ditemukan
            $fallback = $this->resiModel->getAllWithRute(null, 1);
            $resi = $fallback[0] ?? null;
        }

        $checkpoints = [];
        if ($resi) {
            $checkpoints = $this->trackingModel->getCheckpointsByResi($resi['id']);
        }

        $allResi = $this->resiModel->getAllWithRute(null, 30);

        $this->view('tracking/index', [
            'user' => $user,
            'resi' => $resi,
            'checkpoints' => $checkpoints,
            'allResi' => $allResi,
            'pageTitle' => 'Tracking & Serah Terima - KargoTrack'
        ]);
    }

    /**
     * Tambah Checkpoint Perjalanan Baru
     */
    public function storeCheckpoint(): void
    {
        $user = $this->requireAuth();
        // BUG-01: Verifikasi CSRF
        if (!$this->verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'Token keamanan tidak valid.'], 403);
        }
        $resiId = (int)($_POST['resi_id'] ?? 0);

        $resi = $this->resiModel->find($resiId);
        if (!$resi) {
            $this->json(['success' => false, 'message' => 'Resi tidak ditemukan.'], 404);
        }

        $status = trim($_POST['status'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');
        // BUG-21 FIX: Validasi format datetime sebelum menyimpan ke database
        $waktuRaw = trim($_POST['waktu'] ?? '');
        $waktu = date('Y-m-d H:i:s'); // default sekarang
        if (!empty($waktuRaw)) {
            $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $waktuRaw)
               ?? \DateTime::createFromFormat('Y-m-d\TH:i', $waktuRaw)
               ?? \DateTime::createFromFormat('Y-m-d H:i', $waktuRaw);
            if ($dt && $dt->format('Y') > 1970) {
                $waktu = $dt->format('Y-m-d H:i:s');
            }
        }
        $namaPenerima = trim($_POST['nama_penerima'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');

        if (empty($status) || empty($lokasi)) {
            $this->json(['success' => false, 'message' => 'Status dan lokasi checkpoint wajib diisi.'], 422);
        }

        // Handle Upload Foto Bukti Serah Terima (POD)
        $fotoBuktiName = null;
        if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['foto_bukti'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->json(['success' => false, 'message' => 'Foto bukti gagal diunggah. Silakan coba kembali.'], 422);
            }

            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // BUG-02 FIX: Validasi MIME type dari isi file, bukan hanya ekstensi nama file
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($file['tmp_name']);

            if (!in_array($ext, $allowedExts) || !in_array($realMime, $allowedMimes)) {
                $this->json(['success' => false, 'message' => 'Format foto harus JPG, PNG, atau WEBP.'], 422);
            }

            if ($file['size'] > 5 * 1024 * 1024) { // Maks 5 MB
                $this->json(['success' => false, 'message' => 'Ukuran file foto maksimal 5 MB.'], 422);
            }

            $uploadDir = dirname(__DIR__) . '/public/uploads/pod/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    $this->json(['success' => false, 'message' => 'Folder penyimpanan foto tidak dapat dibuat.'], 500);
                }
            }

            $fotoBuktiName = 'pod_' . $resi['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fotoBuktiName)) {
                $this->json(['success' => false, 'message' => 'Foto bukti gagal disimpan. Silakan coba kembali.'], 500);
            }
        }

        // Handle Tanda Tangan Digital (Base64 dari Canvas)
        $ttdDigital = $_POST['ttd_digital'] ?? null;
        if (empty($ttdDigital) || strlen($ttdDigital) < 50) {
            $ttdDigital = null;
        }

        $checkpointData = [
            'status' => $status,
            'lokasi' => $lokasi,
            'waktu' => $waktu,
            'nama_penerima' => !empty($namaPenerima) ? $namaPenerima : null,
            // BUG-27 FIX: Hapus hardcoded nama developer sebagai fallback
            'petugas' => ($user['nama'] ?? 'Petugas') . ' - Petugas',
            'keterangan' => $keterangan,
            'foto_bukti' => $fotoBuktiName,
            'ttd_digital' => $ttdDigital
        ];

        $cpId = $this->trackingModel->addCheckpoint($resiId, $checkpointData);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "Checkpoint '{$status}' berhasil ditambahkan.",
                'checkpoint_id' => $cpId,
                'no_resi' => $resi['no_resi']
            ]);
        }

        $this->setFlash('success', "Checkpoint baru berhasil dicatat.");
        $this->redirect('tracking?resi=' . urlencode($resi['no_resi']));
    }

    /**
     * Pelacakan Publik Tanpa Login (Cek Resi Tanpa Login)
     */
    public function publicTrack(?string $noResi = null): void
    {
        if (empty($noResi)) {
            $noResi = trim($_GET['resi'] ?? '');
        }

        $resi = null;
        $checkpoints = [];

        if (!empty($noResi)) {
            $resi = $this->resiModel->getDetail($noResi);
            if ($resi) {
                $checkpoints = $this->trackingModel->getCheckpointsByResi($resi['id']);
            }
        }

        $this->view('tracking/public', [
            'noResi' => $noResi,
            'resi' => $resi,
            'checkpoints' => $checkpoints,
            'pageTitle' => 'Lacak Resi Kargo Publik - KargoTrack'
        ], null); // Tanpa layout dashboard admin
    }

    /**
     * JSON Live API Tracking Data
     * BUG-05 FIX: Batasi field yang dikembalikan agar tidak bocorkan PII pengirim/penerima
     */
    public function apiTrack(string $noResi): void
    {
        $cleanResi = trim($noResi);
        $resi = $this->resiModel->getDetail($cleanResi);

        if (!$resi) {
            $this->json(['success' => false, 'message' => "Nomor resi {$cleanResi} tidak ditemukan di sistem."], 404);
        }

        $checkpoints = $this->trackingModel->getCheckpointsByResi($resi['id']);
        $publicCheckpoints = array_map(static function (array $checkpoint): array {
            return [
                'status' => $checkpoint['status'],
                'lokasi' => $checkpoint['lokasi'],
                'waktu' => $checkpoint['waktu']
            ];
        }, $checkpoints);

        // Kembalikan hanya data publik (bukan PII penuh pengirim/penerima)
        $publicResi = [
            'no_resi'           => $resi['no_resi'],
            'layanan'           => $resi['layanan'],
            'status_pengiriman' => $resi['status_pengiriman'],
            'jumlah_koli'       => $resi['jumlah_koli'],
            'berat_dikenakan'   => $resi['berat_dikenakan'],
            'estimasi_hari'     => $resi['estimasi_hari'] ?? null,
            'kode_rute'         => $resi['kode_rute'],
            'asal_nama'         => $resi['asal_nama'],
            'asal_kode'         => $resi['asal_kode'],
            'tujuan_nama'       => $resi['tujuan_nama'],
            'tujuan_kode'       => $resi['tujuan_kode'],
            // Nama penerima dan kota tujuan boleh tampil, tapi bukan nomor HP/alamat lengkap
            'nama_penerima'     => $resi['nama_penerima'],
            'no_manifest'       => $resi['no_manifest'] ?? null,
        ];

        $this->json([
            'success'     => true,
            'resi'        => $publicResi,
            'checkpoints' => $publicCheckpoints
        ]);
    }
}
