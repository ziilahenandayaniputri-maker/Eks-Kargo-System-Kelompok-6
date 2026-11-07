<?php
/**
 * KargoTrack - Industrial Standard PHP Native Front Controller
 * Architecture: Clean MVC (core/, models/, controllers/, views/, public/)
 */

declare(strict_types=1);

// Define Base Paths
define('ROOT_DIR', dirname(__DIR__));
define('CORE_DIR', ROOT_DIR . '/core');
define('APP_START', microtime(true));

// Require Core Components
require_once CORE_DIR . '/helpers.php';
require_once CORE_DIR . '/Autoloader.php';
require_once CORE_DIR . '/Security.php';
require_once CORE_DIR . '/View.php';

use Core\Autoloader;
use Core\App;

// Register Autoloader for Core, Models, Controllers, Services
Autoloader::register();

// Run Database Auto-Seeder sekali saja menggunakan file sentinel agar tidak memperlambat
// setiap request (BUG-07 FIX). Jika file sentinel tidak ada, jalankan seeder dan buat sentinel.
$seedSentinel = ROOT_DIR . '/database/.seeded';
if (!file_exists($seedSentinel) && file_exists(ROOT_DIR . '/database/seed.php')) {
    require_once ROOT_DIR . '/database/seed.php';
    // Buat file sentinel agar seeder tidak dijalankan lagi
    @file_put_contents($seedSentinel, date('Y-m-d H:i:s'));
}

// Register Application Routes
// 1. Dashboard & Wilayah Rute Modul
App::get('', 'WilayahController@index');
App::get('wilayah', 'WilayahController@index');
App::post('wilayah/store', 'WilayahController@store');
App::post('wilayah/update', 'WilayahController@update');
App::post('wilayah/delete', 'WilayahController@delete');
App::get('wilayah/search', 'WilayahController@search');

// 2. Master Rute & Tarif
App::post('rute/store', 'RuteController@store');
App::post('rute/update', 'RuteController@update');
App::post('rute/delete', 'RuteController@delete');
App::get('rute/search', 'RuteController@search');

// 3. Kalkulator Tarif Endpoint
App::get('kalkulator/calculate', 'KalkulatorController@calculate');
App::post('kalkulator/calculate', 'KalkulatorController@calculate');

// 4. Resi & Penerbitan Paket
App::get('resi', 'ResiController@index');
App::post('resi/store', 'ResiController@store');
App::get('resi/search', 'ResiController@search');
App::get('resi/detail/{id}', 'ResiController@detail');
App::get('resi/printReceipt/{id}/{format}', 'ResiController@printReceipt');
App::get('resi/printReceipt/{id}', 'ResiController@printReceipt');
App::post('resi/delete', 'ResiController@delete');

// 5. Tracking & Checkpoint Serah Terima
App::get('tracking', 'TrackingController@index');
App::post('tracking/storeCheckpoint', 'TrackingController@storeCheckpoint');
App::get('tracking/publicTrack', 'TrackingController@publicTrack');
App::get('tracking/apiTrack/{no_resi}', 'TrackingController@apiTrack');

// 6. Manifest Muatan Armada Truk
App::get('manifest', 'ManifestController@index');
App::post('manifest/store', 'ManifestController@store');
App::post('manifest/berangkatkan', 'ManifestController@berangkatkan');

// 7. Otentikasi & Keamanan Sesi
App::get('auth/login', 'AuthController@login');
App::post('auth/authenticate', 'AuthController@authenticate');
App::get('auth/logout', 'AuthController@logout');

// Execute Router
App::run();
