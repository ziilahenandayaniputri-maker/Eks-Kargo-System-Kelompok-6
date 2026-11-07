<?php
/**
 * Database Seeder for KargoTrack
 * BUG-08 FIX: Daftarkan autoloader agar class Core\ lain bisa di-resolve jika dibutuhkan
 * BUG-33 FIX: Tidak lagi perlu require Database.php manual; autoloader menanganinya
 */
require_once __DIR__ . '/../core/Autoloader.php';
\Core\Autoloader::register();

use Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $hostingConfigFile = dirname(__DIR__) . '/config/database.php';
    $hostingConfig = is_file($hostingConfigFile) ? require $hostingConfigFile : [];
    if (!is_array($hostingConfig)) {
        throw new RuntimeException('Database configuration must return an array.');
    }

    $adminEmail = $hostingConfig['admin_email'] ?? 'rizqi@kargotrack.id';
    $adminPassword = $hostingConfig['admin_password'] ?? 'admin123';

    // 1. Seed Users (Hanya jika user belum ada, agar tidak boros CPU bcrypt hashing pada setiap request)
    $userStmt = $db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
    $userStmt->execute([':email' => $adminEmail]);
    $userCount = $userStmt->fetchColumn();
    if ($userCount == 0) {
        $passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT);
        $userStmt = $db->prepare("
            INSERT INTO users (nama, email, password, role, cabang, status)
            VALUES (:nama, :email, :password, :role, :cabang, 'Aktif')
        ");
        $userStmt->execute([
            ':nama' => $hostingConfig['admin_name'] ?? 'Admin',
            ':email' => $adminEmail,
            ':password' => $passwordHash,
            ':role' => 'Admin Gudang',
            ':cabang' => 'Cabang Cirebon'
        ]);
    }

    // Check if Wilayah exists
    $count = $db->query("SELECT COUNT(*) FROM wilayah")->fetchColumn();
    if ($count == 0) {
        $wilayahData = [
            ['CRB', 'Cirebon', 'Jawa Barat', 'Cabang Utama', 'Jl. Brigjen Dharsono No. 88, Cirebon', '0231-480221'],
            ['JKT', 'Jakarta', 'DKI Jakarta', 'Hub Transit', 'Kawasan Pergudangan Pluit Blok C No. 4, Jakarta', '021-669213'],
            ['BDG', 'Bandung', 'Jawa Barat', 'Hub Transit', 'Jl. Soekarno Hatta No. 452, Bandung', '022-756182'],
            ['SBY', 'Surabaya', 'Jawa Timur', 'Hub Transit', 'Pergudangan Margomulyo Indah Kav 9, Surabaya', '031-749102'],
            ['SMG', 'Semarang', 'Jawa Tengah', 'Drop Point', 'Kawasan Industri Candi Blok 10A, Semarang', '024-762901'],
            ['MDN', 'Medan', 'Sumatera Utara', 'Hub Transit', 'Kawasan Industri Medan KIM II Blok B, Medan', '061-685190'],
            ['BKS', 'Bekasi', 'Jawa Barat', 'Hub Transit', 'Kawasan Industri MM2100 Cikarang Barat, Bekasi', '021-898012'],
            ['TGR', 'Tangerang', 'Banten', 'Drop Point', 'Jl. Daan Mogot Km 19, Batuceper, Tangerang', '021-551982'],
            ['SOC', 'Solo', 'Jawa Tengah', 'Drop Point', 'Jl. Slamet Riyadi No. 320, Surakarta', '0271-712345'],
            ['YOG', 'Yogyakarta', 'DI Yogyakarta', 'Drop Point', 'Jl. Ringroad Utara No. 15, Sleman', '0274-889912']
        ];
        $stmtW = $db->prepare("INSERT INTO wilayah (kode_kota, nama_kota, provinsi, tipe, alamat_gudang, telepon) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($wilayahData as $w) {
            $stmtW->execute($w);
        }
    }

    // Check if Rute exists
    $countR = $db->query("SELECT COUNT(*) FROM rute")->fetchColumn();
    if ($countR == 0) {
        $ruteData = [
            ['CRB-JKT', 1, 2, 4500.00, 1.00, '1-2 hari', 'Aktif', 'Rute reguler via Tol Cipali - Cikampek'],
            ['CRB-BDG', 1, 3, 3800.00, 1.00, '1 hari', 'Aktif', 'Rute harian via Tol Cisumdawu'],
            ['CRB-SBY', 1, 4, 7200.00, 5.00, '2-3 hari', 'Aktif', 'Trans-Jawa Express via Semarang - Solo'],
            ['CRB-SMG', 1, 5, 5600.00, 1.50, '2 hari', 'Jadwal terbatas', 'Jadwal keberangkatan Selasa & Kamis malam'],
            ['CRB-MDN', 1, 6, 14500.00, 10.00, '5-8 hari', 'Nonaktif', 'Kargo kapal roro via Merak - Bakauheni'],
            ['CRB-BKS', 1, 7, 4200.00, 1.00, '1 hari', 'Aktif', 'Sortir hub Cikarang'],
            ['CRB-TGR', 1, 8, 4800.00, 1.00, '1-2 hari', 'Aktif', 'Transit hub Pluit'],
            ['CRB-YOG', 1, 10, 5800.00, 2.00, '2 hari', 'Aktif', 'Via jalur lintas selatan']
        ];
        $stmtR = $db->prepare("INSERT INTO rute (kode_rute, kota_asal_id, kota_tujuan_id, tarif_per_kg, min_berat_kg, estimasi_hari, status, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($ruteData as $r) {
            $stmtR->execute($r);
        }
    }

    // Check Manifest
    $countM = $db->query("SELECT COUNT(*) FROM manifest")->fetchColumn();
    if ($countM == 0) {
        $db->exec("
            INSERT INTO manifest (id, no_manifest, no_polisi, nama_sopir, telp_sopir, kota_asal_id, kota_tujuan_id, tgl_keberangkatan, estimasi_tiba, status, catatan)
            VALUES (1, 'MF-0231', 'B 9127 KCD', 'Supriyadi Santoso', '0813-8899-7711', 1, 2, '2026-10-30 14:30:00', '2026-10-31 06:00:00', 'Dalam Perjalanan', 'Truk Fuso Box CDD 8 Ton - Rute Cirebon ke Hub Bekasi & Jakarta')
        ");
    }

    // Check Resi
    $countResi = $db->query("SELECT COUNT(*) FROM resi")->fetchColumn();
    if ($countResi == 0) {
        $db->exec("
            INSERT INTO resi (id, no_resi, rute_id, nama_pengirim, telp_pengirim, alamat_pengirim, nama_penerima, telp_penerima, alamat_penerima, kode_pos_penerima, isi_paket, nilai_barang, jumlah_koli, berat_aktual, panjang, lebar, tinggi, berat_volume, berat_dikenakan, layanan, total_ongkir, status_pengiriman, manifest_id, petugas, created_at)
            VALUES 
            (1, 'KT-CRB-261030-0047', 1, 'CV Mitra Toni Jaya', '0231 480 221', 'Kawasan Sentra Bisnis Cirebon Kav. 12', 'Dewi Lestari', '0812-3489-9912', 'Jl. Cendana Raya No. 12, Kebayoran Baru, Jakarta Selatan', '12150', 'Pupuk organik, 2 koli', 3500000.00, 2, 14.00, 60.00, 40.00, 50.00, 40.00, 40.00, 'Reguler', 180000.00, 'Dalam perjalanan', 1, 'Rizqi - Admin Gudang', '2026-10-30 09:14:00'),
            (2, 'KT-CRB-261029-0012', 2, 'PT Sumber Logam Perkasa', '0812-9900-1122', 'Jl. Tuparev No. 45, Cirebon', 'Bambang Wijaya', '0818-7766-5544', 'Kawasan Industri Batununggal Blok A-4, Bandung', '40266', 'Suku Cadang Mesin Bubut (1 Peti Kayu)', 12500000.00, 1, 25.00, 50.00, 40.00, 30.00, 10.00, 25.00, 'Express Kargo', 95000.00, 'Diterima penerima', NULL, 'Rizqi - Admin Gudang', '2026-10-29 08:30:00'),
            (3, 'KT-CRB-261030-0089', 3, 'Sentra Batik Trusmi', '0231-321456', 'Jl. Trusmi Kulon No. 148, Plered, Cirebon', 'Ibu Nurul Hidayah', '0857-1122-3344', 'Pasar Turi Baru Blok B Lt. 2 No. 88, Surabaya', '60174', 'Kain Batik Cap & Tulis 5 Roll', 8400000.00, 3, 32.00, 70.00, 45.00, 40.00, 21.00, 32.00, 'Reguler', 230400.00, 'Paket diterima gudang', NULL, 'Rizqi - Admin Gudang', '2026-10-30 10:45:00')
        ");
    }

    // Check Tracking Checkpoints
    $countC = $db->query("SELECT COUNT(*) FROM tracking_checkpoints")->fetchColumn();
    if ($countC == 0) {
        $db->exec("
            INSERT INTO tracking_checkpoints (resi_id, status, lokasi, waktu, nama_penerima, petugas, keterangan, foto_bukti, ttd_digital)
            VALUES 
            (1, 'Resi diterbitkan', 'Gudang Cirebon', '2026-10-30 09:14:00', NULL, 'Rizqi - Admin Gudang', 'Resi baru diterbitkan di sistem kargo', NULL, NULL),
            (1, 'Paket diterima gudang', 'Gudang Cirebon', '2026-10-30 11:02:00', NULL, 'Rizqi', 'Barang ditimbang, dicek fisik, dan ditempel label barcode', NULL, NULL),
            (1, 'Dimuat ke truk B 9127 KCD', 'Gudang Cirebon', '2026-10-30 14:30:00', NULL, 'Supriyadi (Sopir)', 'Manifest MF-0231', NULL, NULL),
            (1, 'Tiba di hub Bekasi', 'Hub Transit Bekasi', '2026-10-30 20:05:00', NULL, 'Aris (Koordinator Hub)', 'Transit sortir paket', NULL, NULL),
            (2, 'Resi diterbitkan', 'Gudang Cirebon', '2026-10-29 08:30:00', NULL, 'Rizqi', 'Penerbitan resi kargo express', NULL, NULL),
            (2, 'Paket diterima gudang', 'Gudang Cirebon', '2026-10-29 09:15:00', NULL, 'Petugas Timbang', 'Penimbangan 25 kg peti kayu terverifikasi', NULL, NULL),
            (2, 'Dimuat ke truk D 8821 FB', 'Gudang Cirebon', '2026-10-29 11:00:00', NULL, 'Yayan (Driver)', 'Manifest MF-0228 rute Cirebon - Bandung', NULL, NULL),
            (2, 'Tiba di hub Bandung', 'Hub Utama Soekarno Hatta', '2026-10-29 15:45:00', NULL, 'Admin Sortir BDG', 'Bongkar muat & sortir tujuan Bandung Timur', NULL, NULL),
            (2, 'Dikirim ke alamat penerima', 'Bandung Selatan', '2026-10-30 08:15:00', NULL, 'Kurir Kargo: Deden', 'Dibawa armada L300 Box kurir', NULL, NULL),
            (2, 'Diterima penerima', 'Batununggal, Bandung', '2026-10-30 11:20:00', 'Bambang Wijaya', 'Kurir: Deden', 'Paket telah diserahterimakan dalam kondisi baik dan tersegel utuh.', 'sample_pod_1.jpg', 'data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"200\" height=\"80\"><path d=\"M10,50 Q40,10 70,50 T130,50 T180,30\" fill=\"none\" stroke=\"%230d6b6e\" stroke-width=\"3\"/></svg>')
        ");
    }

    if (php_sapi_name() === 'cli') {
        echo "Seed completed successfully.\n";
    }
} catch (\Exception $e) {
    if (php_sapi_name() === 'cli') {
        echo "Seed error: " . $e->getMessage() . "\n";
    }
}
