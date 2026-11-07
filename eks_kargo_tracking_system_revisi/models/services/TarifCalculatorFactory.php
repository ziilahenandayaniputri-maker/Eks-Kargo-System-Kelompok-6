<?php
namespace Services;

/**
 * Factory Method Pattern (OOP Encapsulation & Polymorphism)
 * Memproduksi instance TarifCalculatorInterface berdasarkan nama jenis layanan
 */
class TarifCalculatorFactory
{
    public static function create(?string $layanan = 'Reguler'): TarifCalculatorInterface
    {
        $normalized = strtolower(trim((string)$layanan));

        if (str_contains($normalized, 'express')) {
            return new ExpressTarifCalculator();
        }

        if (str_contains($normalized, 'trucking')) {
            return new TruckingTarifCalculator();
        }

        return new RegulerTarifCalculator();
    }
}

