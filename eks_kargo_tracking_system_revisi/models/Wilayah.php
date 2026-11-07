<?php
namespace Models;

use Core\Model;

/**
 * Model Master Wilayah Operasional
 */
class Wilayah extends Model
{
    protected string $table = 'wilayah';

    /**
     * Cari wilayah berdasarkan kata kunci (Live Search)
     */
    public function search(string $keyword): array
    {
        $clean = "%" . trim($keyword) . "%";
        $sql = "SELECT * FROM `{$this->table}` 
                WHERE `kode_kota` LIKE :k1 
                   OR `nama_kota` LIKE :k2 
                   OR `provinsi` LIKE :k3 
                   OR `tipe` LIKE :k4 
                ORDER BY `nama_kota` ASC";

        return $this->query($sql, [
            ':k1' => $clean,
            ':k2' => $clean,
            ':k3' => $clean,
            ':k4' => $clean
        ]);
    }

    /**
     * Ambil statistik ringkas wilayah
     */
    public function getSummaryStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_kota,
                    SUM(CASE WHEN tipe = 'Hub Transit' THEN 1 ELSE 0 END) as total_hub,
                    SUM(CASE WHEN tipe = 'Cabang Utama' THEN 1 ELSE 0 END) as total_cabang,
                    SUM(CASE WHEN tipe = 'Drop Point' THEN 1 ELSE 0 END) as total_drop_point
                FROM `{$this->table}` WHERE status = 'Aktif'";
        $res = $this->query($sql);
        return $res[0] ?? ['total_kota' => 0, 'total_hub' => 0, 'total_cabang' => 0, 'total_drop_point' => 0];
    }
}

