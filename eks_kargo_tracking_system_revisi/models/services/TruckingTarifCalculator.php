<?php
namespace Services;

/**
 * TruckingTarifCalculator (OOP Polymorphism)
 * Layanan Trucking / Muatan Berat (FTL / LTL) dengan skala diskon tonase
 */
class TruckingTarifCalculator implements TarifCalculatorInterface
{
    private float $divisor = 6000.0;
    private float $minWeight = 10.0;

    public function calculateVolumeWeight(float $panjang, float $lebar, float $tinggi, int $koli = 1): float
    {
        if ($panjang <= 0 || $lebar <= 0 || $tinggi <= 0) {
            return 0.0;
        }
        $singleVolume = ($panjang * $lebar * $tinggi) / $this->divisor;
        return round($singleVolume * max(1, $koli), 1);
    }

    public function getChargeableWeight(float $beratAktual, float $beratVolume): float
    {
        $weight = max($beratAktual, $beratVolume);
        return max($weight, $this->minWeight);
    }

    public function calculateTotalOngkir(float $chargeableWeight, float $tarifPerKg, int $koli = 1): float
    {
        $total = $chargeableWeight * $tarifPerKg;

        // Diskon volume tonase
        if ($chargeableWeight >= 100) {
            $total *= 0.85; // Diskon 15% untuk muatan >= 100 kg
        } elseif ($chargeableWeight >= 50) {
            $total *= 0.90; // Diskon 10% untuk muatan >= 50 kg
        }

        return round($total, 0);
    }

    public function getServiceName(): string
    {
        return 'Trucking FTL';
    }
}

