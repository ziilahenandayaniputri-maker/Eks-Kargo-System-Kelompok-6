<?php
namespace Services;

/**
 * Interface ReceiptRendererInterface (OOP Abstraction)
 * Kontrak untuk mencetak resi dalam berbagai format standar ekspedisi kargo
 */
interface ReceiptRendererInterface
{
    /**
     * Render dokumen resi ke HTML format cetak
     */
    public function render(array $resi): string;

    /**
     * Nama format resi
     */
    public function getFormatName(): string;
}

