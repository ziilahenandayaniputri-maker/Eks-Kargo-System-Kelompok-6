<?php
namespace Services;

use Core\Security;

/**
 * A4ReceiptRenderer (OOP Polymorphism)
 * Menghasilkan Surat Muatan Kargo (Airway Bill) resmi ukuran A4 (3 Rangkap)
 */
class A4ReceiptRenderer implements ReceiptRendererInterface
{
    public function render(array $resi): string
    {
        $noResi = Security::escape($resi['no_resi'] ?? '-');
        $namaPengirim = Security::escape($resi['nama_pengirim'] ?? '-');
        $telpPengirim = Security::escape($resi['telp_pengirim'] ?? '-');
        $alamatPengirim = Security::escape($resi['alamat_pengirim'] ?? '-');
        $namaPenerima = Security::escape($resi['nama_penerima'] ?? '-');
        $telpPenerima = Security::escape($resi['telp_penerima'] ?? '-');
        $alamatPenerima = Security::escape($resi['alamat_penerima'] ?? '-');
        $kodePos = Security::escape($resi['kode_pos_penerima'] ?? '-');
        $layanan = Security::escape($resi['layanan'] ?? 'Reguler');
        $beratAktual = Security::escape($resi['berat_aktual'] ?? 0);
        $beratVolume = Security::escape($resi['berat_volume'] ?? 0);
        $beratDikenakan = Security::escape($resi['berat_dikenakan'] ?? 0);
        $koli = Security::escape($resi['jumlah_koli'] ?? 1);
        $isiPaket = Security::escape($resi['isi_paket'] ?? '-');
        $nilaiBarang = format_rupiah($resi['nilai_barang'] ?? 0);
        $ruteStr = Security::escape(($resi['asal_nama'] ?? 'Cirebon') . ' → ' . ($resi['tujuan_nama'] ?? 'Jakarta'));
        $ongkir = format_rupiah($resi['total_ongkir'] ?? 0);
        $tgl = date('d F Y H:i', strtotime($resi['created_at'] ?? 'now'));
        $petugas = Security::escape($resi['petugas'] ?? 'Admin Gudang');

        return <<<HTML
<div class="receipt-a4-page">
    <div class="border-2 border-gray-800 p-6 rounded-lg bg-white shadow-sm">
        <!-- Header A4 -->
        <div class="flex justify-between items-start border-b-2 border-gray-800 pb-4 mb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded bg-amber-500 text-white font-bold text-lg">K</span>
                    <span class="text-2xl font-black tracking-tight text-teal-900">KargoTrack</span>
                </div>
                <div class="text-xs text-gray-600 mt-1">SURAT MUATAN KARGO & RESI EKSPEDISI ANTAR-KOTA</div>
                <div class="text-[11px] text-gray-500">Cabang Cirebon · Jl. Brigjen Dharsono No. 88, Cirebon (0231-480221)</div>
            </div>
            <div class="text-right">
                <div class="text-sm font-semibold text-gray-700">NO. RESI RESMI</div>
                <div class="text-xl font-mono font-black text-teal-900">{$noResi}</div>
                <svg id="barcode-a4-svg" class="barcode-svg max-w-[200px] h-10 ml-auto" jsbarcode-value="{$noResi}"></svg>
            </div>
        </div>

        <!-- Rincian Ekspedisi Grid -->
        <div class="grid grid-cols-2 gap-4 text-xs mb-4">
            <div class="border border-gray-300 p-3 rounded">
                <div class="font-bold text-gray-800 border-b pb-1 mb-2">PENGIRIM (SHIPPER)</div>
                <div class="font-bold text-sm">{$namaPengirim}</div>
                <div class="text-gray-600">Telp: {$telpPengirim}</div>
                <div class="text-gray-600 mt-1">{$alamatPengirim}</div>
            </div>
            <div class="border border-gray-300 p-3 rounded">
                <div class="font-bold text-gray-800 border-b pb-1 mb-2">PENERIMA (CONSIGNEE)</div>
                <div class="font-bold text-sm">{$namaPenerima}</div>
                <div class="text-gray-600">Telp: {$telpPenerima}</div>
                <div class="text-gray-600 mt-1">{$alamatPenerima}</div>
                <div class="text-gray-500 font-semibold mt-1">Kode Pos: {$kodePos}</div>
            </div>
        </div>

        <!-- Detail Muatan Table -->
        <table class="w-full text-xs border border-gray-300 mb-4">
            <thead class="bg-gray-100 border-b border-gray-300 text-gray-700">
                <tr>
                    <th class="p-2 text-left">Deskripsi Barang</th>
                    <th class="p-2 text-center">Koli</th>
                    <th class="p-2 text-center">Berat Aktual</th>
                    <th class="p-2 text-center">Berat Volume</th>
                    <th class="p-2 text-center">Chargeable Weight</th>
                    <th class="p-2 text-right">Nilai Barang</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-gray-200">
                    <td class="p-2 font-medium">{$isiPaket}</td>
                    <td class="p-2 text-center">{$koli}</td>
                    <td class="p-2 text-center">{$beratAktual} kg</td>
                    <td class="p-2 text-center">{$beratVolume} kg</td>
                    <td class="p-2 text-center font-bold text-teal-900">{$beratDikenakan} kg</td>
                    <td class="p-2 text-right">{$nilaiBarang}</td>
                </tr>
            </tbody>
        </table>

        <!-- Ringkasan Layanan & Ongkir -->
        <div class="grid grid-cols-2 gap-4 items-center border border-gray-300 p-3 rounded mb-6">
            <div class="text-xs space-y-1">
                <div><span class="text-gray-500">Rute Ekspedisi:</span> <span class="font-bold">{$ruteStr}</span></div>
                <div><span class="text-gray-500">Layanan:</span> <span class="font-bold">{$layanan}</span></div>
                <div><span class="text-gray-500">Waktu Terbit:</span> <span>{$tgl}</span></div>
                <div><span class="text-gray-500">Petugas Counter:</span> <span>{$petugas}</span></div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500 font-bold uppercase">Total Biaya Ongkos Kirim</div>
                <div class="text-2xl font-black text-amber-600">{$ongkir}</div>
                <div class="text-[11px] text-green-700 font-medium">LUNAS / NON-COD</div>
            </div>
        </div>

        <!-- Tanda Tangan 3 Kolom -->
        <div class="grid grid-cols-3 gap-4 text-center text-xs mt-6 pt-4 border-t border-gray-200">
            <div>
                <div class="text-gray-600 mb-12">Pengirim</div>
                <div class="border-b border-gray-400 mx-4"></div>
                <div class="mt-1 font-semibold">({$namaPengirim})</div>
            </div>
            <div>
                <div class="text-gray-600 mb-12">Petugas Cabang</div>
                <div class="border-b border-gray-400 mx-4"></div>
                <div class="mt-1 font-semibold">({$petugas})</div>
            </div>
            <div>
                <div class="text-gray-600 mb-12">Penerima Barang (POD)</div>
                <div class="border-b border-gray-400 mx-4"></div>
                <div class="mt-1 font-semibold">({$namaPenerima})</div>
            </div>
        </div>
        
        <div class="mt-6 text-[10px] text-gray-400 text-center">
            Rangkap 1: Pengirim · Rangkap 2: Gudang Logistik · Rangkap 3: Bukti Serah Terima Penerima
        </div>
    </div>
</div>
HTML;
    }

    public function getFormatName(): string
    {
        return 'Format A4 (3-Ply)';
    }
}

