<?php
namespace Models;

use Core\Model;

/**
 * Model Checkpoint Tracking Perjalanan & Status Serah Terima (POD)
 */
class Tracking extends Model
{
    protected string $table = 'tracking_checkpoints';

    /**
     * Ambil seluruh riwayat checkpoint berdasarkan ID Resi
     */
    public function getCheckpointsByResi(int $resiId): array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `resi_id` = :resi_id 
                ORDER BY `waktu` ASC, `id` ASC";

        return $this->query($sql, [':resi_id' => $resiId]);
    }

    /**
     * Tambah Checkpoint Baru
     */
    public function addCheckpoint(int $resiId, array $data): int
    {
        $data['resi_id'] = $resiId;
        if (empty($data['waktu'])) {
            $data['waktu'] = date('Y-m-d H:i:s');
        }

        $checkpointId = $this->create($data);

        // Otomatis sinkronkan status terakhir ke tabel resi
        $resiModel = new Resi();
        $resiModel->updateStatus($resiId, $data['status']);

        return $checkpointId;
    }

    /**
     * Ambil Checkpoint Terakhir untuk 1 Resi
     */
    public function getLatestCheckpoint(int $resiId): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `resi_id` = :resi_id 
                ORDER BY `waktu` DESC, `id` DESC LIMIT 1";

        $res = $this->query($sql, [':resi_id' => $resiId]);
        return $res[0] ?? null;
    }

    /**
     * Daftar tahapan milestone standar kargo
     */
    public static function getStandardMilestones(): array
    {
        return [
            'Resi diterbitkan' => 'Resi baru dibuat dan tercatat di sistem kargo',
            'Paket diterima gudang' => 'Barang ditimbang, dicek fisik & ditempel barcode',
            'Dimuat ke truk' => 'Paket dimasukkan ke manifest armada truk kargo',
            'Tiba di hub transit' => 'Transit sortir di hub operasional antara',
            'Dikirim ke alamat penerima' => 'Dibawa armada kurir pengantaran akhir',
            'Diterima penerima' => 'Paket berhasil diserahterimakan dengan bukti POD'
        ];
    }
}

