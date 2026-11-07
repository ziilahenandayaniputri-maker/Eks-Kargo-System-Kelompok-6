<?php
namespace Services;

/**
 * Interface TarifCalculatorInterface (OOP Abstraction)
 * Mendefinisikan kontrak penghitungan tarif ongkos kirim kargo
 */
interface TarifCalculatorInterface
{
    /**
     * Hitung berat volume kargo darat berdasarkan dimensi cm dan koli
     */
    public function calculateVolumeWeight(float $panjang, float $lebar, float $tinggi, int $koli = 1): float;

    /**
     * Tentukan berat yang dikenakan biaya (Chargeable Weight: terbesar antara aktual vs volume)
     */
    public function getChargeableWeight(float $beratAktual, float $beratVolume): float;

    /**
     * Hitung total biaya ongkir akhir
     */
    public function calculateTotalOngkir(float $chargeableWeight, float $tarifPerKg, int $koli = 1): float;

    /**
     * Nama layanan kargo
     */
    public function getServiceName(): string;
}

