-- Database Schema for KargoTrack
CREATE DATABASE IF NOT EXISTS `kargotrack_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kargotrack_db`;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'Admin Gudang',
  `cabang` VARCHAR(100) NOT NULL DEFAULT 'Cabang Cirebon',
  `status` ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wilayah Table
CREATE TABLE IF NOT EXISTS `wilayah` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_kota` VARCHAR(10) NOT NULL UNIQUE,
  `nama_kota` VARCHAR(100) NOT NULL,
  `provinsi` VARCHAR(100) NOT NULL,
  `tipe` ENUM('Hub Transit', 'Cabang Utama', 'Drop Point') DEFAULT 'Drop Point',
  `alamat_gudang` TEXT,
  `telepon` VARCHAR(30) DEFAULT NULL,
  `status` ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rute Table
CREATE TABLE IF NOT EXISTS `rute` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_rute` VARCHAR(20) NOT NULL UNIQUE,
  `kota_asal_id` INT NOT NULL,
  `kota_tujuan_id` INT NOT NULL,
  `tarif_per_kg` DECIMAL(12,2) NOT NULL,
  `min_berat_kg` DECIMAL(8,2) DEFAULT 1.00,
  `estimasi_hari` VARCHAR(50) NOT NULL,
  `status` ENUM('Aktif', 'Jadwal terbatas', 'Nonaktif') DEFAULT 'Aktif',
  `catatan` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`kota_asal_id`) REFERENCES `wilayah` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`kota_tujuan_id`) REFERENCES `wilayah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Manifest Table
CREATE TABLE IF NOT EXISTS `manifest` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_manifest` VARCHAR(30) NOT NULL UNIQUE,
  `no_polisi` VARCHAR(20) NOT NULL,
  `nama_sopir` VARCHAR(100) NOT NULL,
  `telp_sopir` VARCHAR(30) DEFAULT NULL,
  `kota_asal_id` INT NOT NULL,
  `kota_tujuan_id` INT NOT NULL,
  `tgl_keberangkatan` DATETIME NOT NULL,
  `estimasi_tiba` DATETIME DEFAULT NULL,
  `status` ENUM('Draft', 'Siap Berangkat', 'Dalam Perjalanan', 'Tiba di Tujuan', 'Selesai') DEFAULT 'Dalam Perjalanan',
  `catatan` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`kota_asal_id`) REFERENCES `wilayah` (`id`),
  FOREIGN KEY (`kota_tujuan_id`) REFERENCES `wilayah` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resi Table
CREATE TABLE IF NOT EXISTS `resi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_resi` VARCHAR(35) NOT NULL UNIQUE,
  `rute_id` INT NOT NULL,
  `nama_pengirim` VARCHAR(100) NOT NULL,
  `telp_pengirim` VARCHAR(30) NOT NULL,
  `alamat_pengirim` TEXT,
  `nama_penerima` VARCHAR(100) NOT NULL,
  `telp_penerima` VARCHAR(30) NOT NULL,
  `alamat_penerima` TEXT NOT NULL,
  `kode_pos_penerima` VARCHAR(10) NOT NULL,
  `isi_paket` VARCHAR(255) NOT NULL,
  `nilai_barang` DECIMAL(14,2) DEFAULT 0.00,
  `jumlah_koli` INT DEFAULT 1,
  `berat_aktual` DECIMAL(8,2) NOT NULL,
  `panjang` DECIMAL(8,2) DEFAULT 0.00,
  `lebar` DECIMAL(8,2) DEFAULT 0.00,
  `tinggi` DECIMAL(8,2) DEFAULT 0.00,
  `berat_volume` DECIMAL(8,2) DEFAULT 0.00,
  `berat_dikenakan` DECIMAL(8,2) NOT NULL,
  `layanan` VARCHAR(50) DEFAULT 'Reguler',
  `total_ongkir` DECIMAL(14,2) NOT NULL,
  `status_pengiriman` VARCHAR(50) DEFAULT 'Resi diterbitkan',
  `manifest_id` INT DEFAULT NULL,
  `petugas` VARCHAR(100) DEFAULT 'Rizqi - Admin Gudang',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`rute_id`) REFERENCES `rute` (`id`),
  FOREIGN KEY (`manifest_id`) REFERENCES `manifest` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tracking Checkpoints Table
CREATE TABLE IF NOT EXISTS `tracking_checkpoints` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resi_id` INT NOT NULL,
  `status` VARCHAR(100) NOT NULL,
  `lokasi` VARCHAR(150) NOT NULL,
  `waktu` DATETIME NOT NULL,
  `nama_penerima` VARCHAR(100) DEFAULT NULL,
  `petugas` VARCHAR(100) DEFAULT NULL,
  `keterangan` TEXT,
  `foto_bukti` VARCHAR(255) DEFAULT NULL,
  `ttd_digital` MEDIUMTEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`resi_id`) REFERENCES `resi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

