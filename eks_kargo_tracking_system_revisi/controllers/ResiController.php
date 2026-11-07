<?php
namespace Controllers;

use Core\Controller;
use Models\Resi;
use Models\Rute;
use Models\Tracking;
use Services\TarifCalculatorFactory;
use Services\ReceiptRendererFactory;

/**
 * Controller CRUD Penerbitan Resi Pengiriman & Data Paket Kiriman
 */
class ResiController extends Controller
{
    private Resi $resiModel;
    private Rute $ruteModel;
    private Tracking $trackingModel;

    public function __construct()
    {
        $this->resiModel = new Resi();
        $this->ruteModel = new Rute();
        $this->trackingModel = new Tracking();
    }

    /**
     * Halaman Penerbitan Resi Baru
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $ruteList = $this->ruteModel->getRuteWithWilayah('r.kode_rute ASC');

        // Parameter dari Kalkulator Tarif ("Buat resi dari tarif ini")
        $prefill = [
            'rute_id' => (int)($_GET['rute_id'] ?? 1),
            'panjang' => (float)($_GET['panjang'] ?? 60),
            'lebar' => (float)($_GET['lebar'] ?? 40),
            'tinggi' => (float)($_GET['tinggi'] ?? 50),
            'berat_aktual' => (float)($_GET['berat_aktual'] ?? 14),
            'jumlah_koli' => (int)($_GET['jumlah_koli'] ?? 2),
            'layanan' => $_GET['layanan'] ?? 'Reguler',
            'isi_paket' => $_GET['isi_paket'] ?? 'Pupuk organik, 2 koli',
            'nilai_barang' => (float)($_GET['nilai_barang'] ?? 3500000),
            'nama_pengirim' => $_GET['nama_pengirim'] ?? 'CV Mitra Toni Jaya',
            'telp_pengirim' => $_GET['telp_pengirim'] ?? '0231 480 221',
            'nama_penerima' => $_GET['nama_penerima'] ?? 'Dewi Lestari',
            'telp_penerima' => $_GET['telp_penerima'] ?? '0812-3489-9912',
            'alamat_penerima' => $_GET['alamat_penerima'] ?? 'Jl. Cendana Raya No. 12, Kebayoran Baru, Jakarta Selatan',
            'kode_pos_penerima' => $_GET['kode_pos_penerima'] ?? '12150'
        ];

        // BUG-11 FIX: Gunakan getAllWithRute agar fallback selalu memiliki joined fields
        $latestResi = $this->resiModel->getDetail('KT-CRB-261030-0047');
        if (!$latestResi) {
            $fallback = $this->resiModel->getAllWithRute(null, 1);
            $latestResi = $fallback[0] ?? null;
        }

        $this->view('resi/index', [
            'user' => $user,
            'ruteList' => $ruteList,
            'prefill' => $prefill,
            'latestResi' => $latestResi,
            'pageTitle' => 'Penerbitan Resi Pengiriman - KargoTrack'
        ]);
    }

    /**
     * Simpan Resi Baru (Terbitkan Resi atau Simpan Draf)
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
            $this->redirect('resi');
        }

        $actionType = $_POST['action_type'] ?? 'terbitkan'; // 'terbitkan' atau 'draf'
        $ruteId = (int)($_POST['rute_id'] ?? 0);
        $rute = $this->ruteModel->findWithDetails($ruteId);

        if (!$rute) {
            $this->json(['success' => false, 'message' => 'Rute ekspedisi tidak valid.'], 422);
        }

        $telpPengirim = trim($_POST['telp_pengirim'] ?? '');
        $telpPenerima = trim($_POST['telp_penerima'] ?? '');
        $kodePos = trim($_POST['kode_pos_penerima'] ?? '');

        // Hitung berat volume dan chargeable weight secara OOP di server
        $panjang = (float)($_POST['panjang'] ?? 0);
        $lebar = (float)($_POST['lebar'] ?? 0);
        $tinggi = (float)($_POST['tinggi'] ?? 0);
        $beratAktual = (float)($_POST['berat_aktual'] ?? 1);
        $koli = max(1, (int)($_POST['jumlah_koli'] ?? 1));
        $layanan = $_POST['layanan'] ?? 'Reguler';

        $calculator = TarifCalculatorFactory::create($layanan);
        $beratVolume = $calculator->calculateVolumeWeight($panjang, $lebar, $tinggi, $koli);
        $chargeableWeight = max(
            $calculator->getChargeableWeight($beratAktual, $beratVolume),
            (float)($rute['min_berat_kg'] ?? 1)
        );
        $totalOngkir = $calculator->calculateTotalOngkir($chargeableWeight, (float)$rute['tarif_per_kg'], $koli);

        $data = [
            'rute_id' => $ruteId,
            'nama_pengirim' => trim($_POST['nama_pengirim'] ?? ''),
            'telp_pengirim' => $telpPengirim,
            'alamat_pengirim' => trim($_POST['alamat_pengirim'] ?? 'Gudang Cirebon'),
            'nama_penerima' => trim($_POST['nama_penerima'] ?? ''),
            'telp_penerima' => $telpPenerima,
            'alamat_penerima' => trim($_POST['alamat_penerima'] ?? ''),
            'kode_pos_penerima' => $kodePos,
            'isi_paket' => trim($_POST['isi_paket'] ?? ''),
            'nilai_barang' => (float)($_POST['nilai_barang'] ?? 0),
            'jumlah_koli' => $koli,
            'berat_aktual' => $beratAktual,
            'panjang' => $panjang,
            'lebar' => $lebar,
            'tinggi' => $tinggi,
            'berat_volume' => $beratVolume,
            'berat_dikenakan' => $chargeableWeight,
            'layanan' => $layanan,
            'total_ongkir' => $totalOngkir,
            'status_pengiriman' => ($actionType === 'draf') ? 'Draft' : 'Resi diterbitkan',
        // BUG-28 FIX: Hapus hardcoded nama developer sebagai fallback
            'petugas' => ($user['nama'] ?? 'Petugas') . ' - Admin Gudang'
        ];

        // Validasi ketat data penerima dan pengirim
        // BUG-24 FIX: Tambah phone_id untuk telp_pengirim juga
        // BUG-25 FIX: Tambah validasi untuk field numerik
        $errors = $this->validate($data, [
            'nama_pengirim' => 'required',
            'telp_pengirim' => 'required|phone_id',
            'nama_penerima' => 'required',
            'telp_penerima' => 'required|phone_id',
            'alamat_penerima' => 'required',
            'kode_pos_penerima' => 'required|postal_code',
            'isi_paket' => 'required',
            'berat_aktual' => 'required|numeric|min:1',
        ]);

        if (!empty($errors)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors], 422);
            }
            $this->setFlash('error', 'Validasi formulir resi gagal. Periksa kembali bidang yang ditandai merah.');
            $this->redirect('resi');
        }

        // Generate nomor resi resmi otomatis
        $noResi = $this->resiModel->generateNomorResi($rute['asal_kode'] ?? 'CRB');
        $data['no_resi'] = $noResi;

        $resiId = $this->resiModel->create($data);

        // Jika diterbitkan langsung, tambahkan checkpoint pertama
        if ($actionType !== 'draf') {
            $this->trackingModel->create([
                'resi_id' => $resiId,
                'status' => 'Resi diterbitkan',
                'lokasi' => 'Gudang ' . ($rute['asal_nama'] ?? 'Cirebon'),
                'waktu' => date('Y-m-d H:i:s'),
                'petugas' => $data['petugas'],
                'keterangan' => 'Resi baru diterbitkan di sistem kargo'
            ]);
        }

        $detailResi = $this->resiModel->getDetail($resiId);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "Resi {$noResi} berhasil " . ($actionType === 'draf' ? 'disimpan sebagai draf.' : 'diterbitkan!'),
                'data' => $detailResi,
                'resi_id' => $resiId,
                'no_resi' => $noResi
            ]);
        }

        $this->setFlash('success', "Resi {$noResi} berhasil diterbitkan!");
        $this->redirect('resi');
    }

    /**
     * Live Search & List Seluruh Resi (JSON)
     */
    public function search(): void
    {
        $q = trim($_GET['q'] ?? '');
        $results = !empty($q) ? $this->resiModel->search($q) : $this->resiModel->getAllWithRute(null, 50);
        $this->json(['success' => true, 'data' => $results]);
    }

    /**
     * Ambil Detail 1 Resi (JSON)
     */
    public function detail($idOrNoResi): void
    {
        $resi = $this->resiModel->getDetail($idOrNoResi);
        if (!$resi) {
            $this->json(['success' => false, 'message' => 'Resi tidak ditemukan.'], 404);
        }
        $this->json(['success' => true, 'data' => $resi]);
    }

    /**
     * Cetak Resi (Thermal 58 mm atau A4) via Polymorphic Service
     * BUG-04 FIX: Wajib login untuk mengakses data resi (mencegah PII leak)
     */
    public function printReceipt($id, $format = 'thermal'): void
    {
        $this->requireAuth();
        $resi = $this->resiModel->getDetail($id);
        if (!$resi) {
            die("Resi tidak ditemukan.");
        }

        $renderer = ReceiptRendererFactory::create($format);
        $renderedHtml = $renderer->render($resi);

        $this->view('print/view', [
            'renderedHtml' => $renderedHtml,
            'resi' => $resi,
            'format' => $format,
            'pageTitle' => "Cetak Resi {$resi['no_resi']} ({$renderer->getFormatName()})"
        ], null); // Render stand-alone print page
    }

    /**
     * Hapus Resi
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
            $this->json(['success' => false, 'message' => 'ID resi tidak valid.'], 400);
        }

        $this->resiModel->delete($id);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'message' => 'Resi berhasil dihapus.']);
        }

        $this->setFlash('success', 'Resi berhasil dihapus.');
        $this->redirect('resi');
    }
}
