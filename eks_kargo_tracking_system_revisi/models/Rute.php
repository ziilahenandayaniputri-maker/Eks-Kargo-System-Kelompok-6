<?php
namespace Models;

use Core\Model;

/**
 * Model Master Rute & Tarif
 * Mengelola data relasi antar-wilayah operasional dan kebijakan tarif kargo
 */
class Rute extends Model
{
    protected string $table = 'rute';

    /**
     * Ambil rute beserta informasi detail kota asal dan tujuan
     */
    public function getRuteWithWilayah(string $orderBy = 'r.kode_rute ASC'): array
    {
        $sql = "SELECT 
                    r.*,
                    w_asal.nama_kota as asal_nama,
                    w_asal.kode_kota as asal_kode,
                    w_asal.provinsi as asal_provinsi,
                    w_tujuan.nama_kota as tujuan_nama,
                    w_tujuan.kode_kota as tujuan_kode,
                    w_tujuan.provinsi as tujuan_provinsi
                FROM `{$this->table}` r
                JOIN `wilayah` w_asal ON r.kota_asal_id = w_asal.id
                JOIN `wilayah` w_tujuan ON r.kota_tujuan_id = w_tujuan.id
                ORDER BY {$orderBy}";

        return $this->query($sql);
    }

    /**
     * Ambil 1 detail rute dengan kota asal & tujuan
     */
    public function findWithDetails($id): ?array
    {
        $sql = "SELECT 
                    r.*,
                    w_asal.nama_kota as asal_nama,
                    w_asal.kode_kota as asal_kode,
                    w_asal.provinsi as asal_provinsi,
                    w_tujuan.nama_kota as tujuan_nama,
                    w_tujuan.kode_kota as tujuan_kode,
                    w_tujuan.provinsi as tujuan_provinsi
                FROM `{$this->table}` r
                JOIN `wilayah` w_asal ON r.kota_asal_id = w_asal.id
                JOIN `wilayah` w_tujuan ON r.kota_tujuan_id = w_tujuan.id
                WHERE r.id = :id LIMIT 1";

        $res = $this->query($sql, [':id' => $id]);
        return $res[0] ?? null;
    }

    /**
     * Cari rute live search
     */
    public function search(string $keyword): array
    {
        $clean = "%" . trim($keyword) . "%";
        $sql = "SELECT 
                    r.*,
                    w_asal.nama_kota as asal_nama,
                    w_asal.kode_kota as asal_kode,
                    w_asal.provinsi as asal_provinsi,
                    w_tujuan.nama_kota as tujuan_nama,
                    w_tujuan.kode_kota as tujuan_kode,
                    w_tujuan.provinsi as tujuan_provinsi
                FROM `{$this->table}` r
                JOIN `wilayah` w_asal ON r.kota_asal_id = w_asal.id
                JOIN `wilayah` w_tujuan ON r.kota_tujuan_id = w_tujuan.id
                WHERE r.kode_rute LIKE :k1
                   OR w_asal.nama_kota LIKE :k2
                   OR w_tujuan.nama_kota LIKE :k3
                   OR r.status LIKE :k4
                   OR r.catatan LIKE :k5
                   OR r.tarif_per_kg LIKE :k6
                ORDER BY r.kode_rute ASC";

        return $this->query($sql, [
            ':k1' => $clean,
            ':k2' => $clean,
            ':k3' => $clean,
            ':k4' => $clean,
            ':k5' => $clean,
            ':k6' => $clean
        ]);
    }
}
