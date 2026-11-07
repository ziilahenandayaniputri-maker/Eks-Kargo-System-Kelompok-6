<?php
namespace Services;

use Core\Security;

/**
 * ThermalReceiptRenderer (OOP Polymorphism)
 * Menghasilkan tata letak resi format Thermal Roll 58 mm
 */
class ThermalReceiptRenderer implements ReceiptRendererInterface
{
    public function render(array $resi): string
    {
        $noResi = Security::escape($resi['no_resi'] ?? '-');
        $namaPengirim = Security::escape($resi['nama_pengirim'] ?? '-');
        $telpPengirim = Security::escape($resi['telp_pengirim'] ?? '-');
        $namaPenerima = Security::escape($resi['nama_penerima'] ?? '-');
        $alamatPenerima = Security::escape($resi['alamat_penerima'] ?? '-');
        $layanan = Security::escape($resi['layanan'] ?? 'Reguler');
        $beratInfo = Security::escape(($resi['berat_dikenakan'] ?? 0) . ' kg / ' . ($resi['jumlah_koli'] ?? 1) . ' koli');
        $ruteStr = Security::escape(($resi['asal_kode'] ?? 'CRB') . ' → ' . ($resi['tujuan_kode'] ?? 'JKT'));
        $ongkir = format_rupiah($resi['total_ongkir'] ?? 0);
        $tgl = date('d/m/Y H:i', strtotime($resi['created_at'] ?? 'now'));
        $isiPaket = Security::escape($resi['isi_paket'] ?? '-');

        return <<<HTML
<div class="receipt-thermal-paper">
    <div class="text-center font-bold text-lg tracking-wider">KARGOTRACK</div>
    <div class="text-center text-xs text-gray-600 mb-2">Cabang Cirebon</div>
    
    <div class="border-t border-dashed border-gray-400 my-2"></div>
    
    <div class="barcode-container text-center py-1">
        <svg id="barcode-thermal-svg" class="barcode-svg" jsbarcode-value="{$noResi}"></svg>
        <div class="font-mono text-xs font-bold mt-1">{$noResi}</div>
    </div>
    
    <div class="border-t border-dashed border-gray-400 my-2"></div>
    
    <div class="text-xs space-y-1">
        <div class="flex justify-between">
            <span class="text-gray-500">Tgl</span>
            <span class="font-medium">{$tgl}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Rute</span>
            <span class="font-bold">{$ruteStr}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Layanan</span>
            <span class="font-medium">{$layanan}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Berat</span>
            <span class="font-medium">{$beratInfo}</span>
        </div>
    </div>
    
    <div class="border-t border-dashed border-gray-400 my-2"></div>
    
    <div class="text-xs">
        <div class="text-gray-500 font-semibold">Penerima</div>
        <div class="font-bold">{$namaPenerima}</div>
        <div class="text-gray-600 text-[11px] leading-tight">{$alamatPenerima}</div>
    </div>
    
    <div class="border-t border-dashed border-gray-400 my-2"></div>
    
    <div class="flex justify-between items-center text-xs">
        <span class="font-bold">Ongkir</span>
        <span class="font-bold text-sm">{$ongkir}</span>
    </div>
    
    <div class="border-t border-dashed border-gray-400 my-2"></div>
    
    <div class="text-[10px] text-center text-gray-500">
        Lacak: kargotrack.id/{$noResi}
    </div>
</div>
HTML;
    }

    public function getFormatName(): string
    {
        return 'Thermal 58 mm';
    }
}

