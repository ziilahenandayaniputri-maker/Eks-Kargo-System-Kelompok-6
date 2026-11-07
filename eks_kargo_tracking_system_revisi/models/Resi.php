<?php
namespace Models;

use Core\Model;

/**
 * Model Resi Pengiriman & Data Paket Kiriman
 */
class Resi extends Model
{
    protected string $table = 'resi';

    /**
     * Generate Nomor Resi Standar KargoTrack
     * Format: KT-[ASAL]-[YYMMDD]-[4 DIGIT URUT]
     * Contoh: KT-CRB-261030-0047
     */
    public function generateNomorResi(string $asalKode = 'CRB'): string
    {
        $prefix = 'KT-' . strtoupper($asalKode) . '-' . date('ymd');
        
        $sql = "SELECT no_resi FROM `{$this->table}` 
                WHERE no_resi LIKE :prefix 
                ORDER BY id DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        if ($last) {
            $parts = explode('-', $last);
            $lastSeq = (int)end($parts);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . '-' . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Ambil Detail Lengkap 1 Resi (berdasarkan ID atau Nomor Resi)
     */
    public function getDetail($idOrNoResi): ?array
    {
        $isNumeric = is_numeric($idOrNoResi);
        $whereClause = $isNumeric ? "r.id = :val" : "r.no_resi = :val";

        $sql = "SELECT 
                    r.*,
                    rt.kode_rute,
                    rt.tarif_per_kg,
                    rt.estimasi_hari,
                    w_asal.nama_kota as asal_nama,
                    w_asal.kode_kota as asal_kode,
                    w_tujuan.nama_kota as tujuan_nama,
                    w_tujuan.kode_kota as tujuan_kode,
                    m.no_manifest,
                    m.no_polisi,
                    m.nama_sopir
                FROM `{$this->table}` r
                JOIN `rute` rt ON r.rute_id = rt.id
                JOIN `wilayah` w_asal ON rt.kota_asal_id = w_asal.id
                JOIN `wilayah` w_tujuan ON rt.kota_tujuan_id = w_tujuan.id
                LEFT JOIN `manifest` m ON r.manifest_id = m.id
                WHERE {$whereClause} LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':val' => $idOrNoResi]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Ambil Semua Resi dengan Join Rute
     */
    public function getAllWithRute(?string $status = null, ?int $limit = null): array
    {
        $sql = "SELECT 
                    r.*,
                    rt.kode_rute,
                    w_asal.nama_kota as asal_nama,
                    w_asal.kode_kota as asal_kode,
                    w_tujuan.nama_kota as tujuan_nama,
                    w_tujuan.kode_kota as tujuan_kode
                FROM `{$this->table}` r
                JOIN `rute` rt ON r.rute_id = rt.id
                JOIN `wilayah` w_asal ON rt.kota_asal_id = w_asal.id
                JOIN `wilayah` w_tujuan ON rt.kota_tujuan_id = w_tujuan.id";

        $params = [];
        if ($status) {
            $sql .= " WHERE r.status_pengiriman = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY r.id DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->query($sql, $params);
    }

    /**
     * Cari Resi (Live Search)
     */
    public function search(string $keyword): array
    {
        $clean = "%" . trim($keyword) . "%";
        $sql = "SELECT 
                    r.*,
                    rt.kode_rute,
                    w_asal.nama_kota as asal_nama,
                    w_asal.kode_kota as asal_kode,
                    w_tujuan.nama_kota as tujuan_nama,
                    w_tujuan.kode_kota as tujuan_kode
                FROM `{$this->table}` r
                JOIN `rute` rt ON r.rute_id = rt.id
                JOIN `wilayah` w_asal ON rt.kota_asal_id = w_asal.id
                JOIN `wilayah` w_tujuan ON rt.kota_tujuan_id = w_tujuan.id
                WHERE r.no_resi LIKE :k1
                   OR r.nama_pengirim LIKE :k2
                   OR r.nama_penerima LIKE :k3
                   OR r.telp_penerima LIKE :k4
                   OR r.isi_paket LIKE :k5
                   OR r.status_pengiriman LIKE :k6
                ORDER BY r.id DESC";

        return $this->query($sql, [
            ':k1' => $clean,
            ':k2' => $clean,
            ':k3' => $clean,
            ':k4' => $clean,
            ':k5' => $clean,
            ':k6' => $clean
        ]);
    }

    /**
     * Ambil resi siap muat: status 'Resi diterbitkan' atau 'Paket diterima gudang'
     * dan belum masuk manifest (manifest_id IS NULL)
     * BUG-23 FIX
     */
    public function getUnassignedReadyResi(): array
    {
        $sql = "SELECT r.*,
                    rt.kode_rute,
                    w_asal.nama_kota as asal_nama,
                    w_asal.kode_kota as asal_kode,
                    w_tujuan.nama_kota as tujuan_nama,
                    w_tujuan.kode_kota as tujuan_kode
                FROM `{$this->table}` r
                JOIN `rute` rt ON r.rute_id = rt.id
                JOIN `wilayah` w_asal ON rt.kota_asal_id = w_asal.id
                JOIN `wilayah` w_tujuan ON rt.kota_tujuan_id = w_tujuan.id
                WHERE r.status_pengiriman IN ('Resi diterbitkan', 'Paket diterima gudang')
                  AND (r.manifest_id IS NULL OR r.manifest_id = 0)
                ORDER BY r.id DESC";
        return $this->query($sql);
    }


    public function updateStatus(int $resiId, string $status): bool
    {
        return $this->update($resiId, ['status_pengiriman' => $status]);
    }

    /**
     * Statistik KPI Resi Logistik
     */
    public function getKpiStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_resi,
                    SUM(CASE WHEN status_pengiriman = 'Dalam perjalanan' THEN 1 ELSE 0 END) as dalam_perjalanan,
                    SUM(CASE WHEN status_pengiriman = 'Diterima penerima' THEN 1 ELSE 0 END) as terkirim,
                    SUM(CASE WHEN status_pengiriman = 'Resi diterbitkan' THEN 1 ELSE 0 END) as baru,
                    SUM(berat_dikenakan) as total_berat_kg,
                    SUM(total_ongkir) as total_pendapatan
                FROM `{$this->table}`";

        $res = $this->query($sql);
        return $res[0] ?? [
            'total_resi' => 0,
            'dalam_perjalanan' => 0,
            'terkirim' => 0,
            'baru' => 0,
            'total_berat_kg' => 0,
            'total_pendapatan' => 0
        ];
    }
}

