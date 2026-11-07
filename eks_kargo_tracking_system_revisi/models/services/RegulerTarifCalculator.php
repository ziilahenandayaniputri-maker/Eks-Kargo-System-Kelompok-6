<?php
namespace Services;

/**
 * RegulerTarifCalculator (OOP Polymorphism)
 * Layanan reguler kargo darat standar:
 * Rumus Volume = (P * L * T / 6000) * Koli
 * Chargeable Weight = max(Berat Aktual, Berat Volume)
 */
class RegulerTarifCalculator implements TarifCalculatorInterface
{
    private float $divisor = 6000.0;
    private float $multiplier = 1.0;
    private float $minWeight = 1.0;

    public function calculateVolumeWeight(float $panjang, float $lebar, float $tinggi, int $koli = 1): float
    {
        if ($panjang <= 0 || $lebar <= 0 || $tinggi <= 0) {
            return 0.0;
        }
        $singleVolume = ($panjang * $lebar * $tinggi) / $this->divisor;
        // Dibulatkan ke 1 desimal
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
        return 'Reguler';
    }
}

