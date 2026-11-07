<?php
namespace Services;

/**
 * ExpressTarifCalculator (OOP Polymorphism)
 * Layanan Express Kargo dengan prioritas armada dan kecepatan tinggi
 */
class ExpressTarifCalculator implements TarifCalculatorInterface
{
    private float $divisor = 5000.0; // Standar volumetrik express
    private float $multiplier = 1.35; // Tarif express 35% lebih tinggi
    private float $minWeight = 2.0;

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
        $baseTotal = $chargeableWeight * $tarifPerKg * $this->multiplier;
        return round($baseTotal, 0);
    }

    public function getServiceName(): string
    {
        return 'Express Kargo';
    }
}

