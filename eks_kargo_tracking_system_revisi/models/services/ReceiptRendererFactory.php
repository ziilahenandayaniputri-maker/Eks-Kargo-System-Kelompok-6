<?php
namespace Services;

/**
 * ReceiptRendererFactory (OOP Encapsulation & Factory Pattern)
 */
class ReceiptRendererFactory
{
    public static function create(string $format = 'thermal'): ReceiptRendererInterface
    {
        $normalized = strtolower(trim($format));
        if ($normalized === 'a4') {
            return new A4ReceiptRenderer();
        }
        return new ThermalReceiptRenderer();
    }
}

