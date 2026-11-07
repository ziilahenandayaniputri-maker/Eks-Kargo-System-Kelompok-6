<?php
namespace Controllers;

use Core\Controller;
use Models\Rute;
use Services\TarifCalculatorFactory;

/**
 * Controller Kalkulator Tarif Kargo (OOP Polymorphism Engine)
 */
class KalkulatorController extends Controller
{
    private Rute $ruteModel;

    public function __construct()
    {
        $this->ruteModel = new Rute();
    }

    /**
     * Hitung Tarif Real-Time via AJAX / API
     */
    public function calculate(): void
    {
        $ruteId = (int)($_POST['rute_id'] ?? $_GET['rute_id'] ?? 0);
        $panjang = (float)($_POST['panjang'] ?? $_GET['panjang'] ?? 0);
        $lebar = (float)($_POST['lebar'] ?? $_GET['lebar'] ?? 0);
        $tinggi = (float)($_POST['tinggi'] ?? $_GET['tinggi'] ?? 0);
        $beratAktual = (float)($_POST['berat_aktual'] ?? $_GET['berat_aktual'] ?? 1);
        $koli = max(1, (int)($_POST['jumlah_koli'] ?? $_GET['jumlah_koli'] ?? 1));
        $layanan = $_POST['layanan'] ?? $_GET['layanan'] ?? 'Reguler';

        $rute = $this->ruteModel->findWithDetails($ruteId);
        if (!$rute) {
            $this->json(['success' => false, 'message' => 'Rute pengiriman tidak ditemukan.'], 404);
        }

        // Gunakan Polymorphic Tarif Calculator Factory
        $calculator = TarifCalculatorFactory::create($layanan);
        $beratVolume = $calculator->calculateVolumeWeight($panjang, $lebar, $tinggi, $koli);
        $chargeableWeight = max(
            $calculator->getChargeableWeight($beratAktual, $beratVolume),
            (float)($rute['min_berat_kg'] ?? 1)
        );
        $totalOngkir = $calculator->calculateTotalOngkir($chargeableWeight, (float)$rute['tarif_per_kg'], $koli);

        $beratAlami = max($beratAktual, $beratVolume);
        $beratDikenakanTipe = $chargeableWeight > $beratAlami
            ? 'minimum'
            : ($beratVolume > $beratAktual ? 'volume' : 'aktual');

        $this->json([
            'success' => true,
            'data' => [
                'rute_id' => $rute['id'],
                'kode_rute' => $rute['kode_rute'],
                'asal_nama' => $rute['asal_nama'],
                'tujuan_nama' => $rute['tujuan_nama'],
                'tarif_per_kg' => (float)$rute['tarif_per_kg'],
                'panjang' => $panjang,
                'lebar' => $lebar,
                'tinggi' => $tinggi,
                'berat_aktual' => $beratAktual,
                'jumlah_koli' => $koli,
                'berat_volume' => $beratVolume,
                'berat_dikenakan' => $chargeableWeight,
                'berat_dikenakan_tipe' => $beratDikenakanTipe,
                'layanan' => $layanan,
                'total_ongkir' => $totalOngkir,
                'total_ongkir_formatted' => format_rupiah($totalOngkir)
            ]
        ]);
    }
}
