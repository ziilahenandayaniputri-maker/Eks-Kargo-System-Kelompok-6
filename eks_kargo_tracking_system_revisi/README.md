# KargoTrack - Sistem Informasi Pelacakan Kargo, Manifest Muatan & Verifikasi Resi Logistik

Aplikasi berbasis web **Desktop sekaligus Mobile** untuk Studi Kasus: **Ekspedisi & Jasa Pengiriman Kargo Antar-Kota**.
Dibangun menggunakan **PHP Native Berstandar Industri** dengan Pola **MVC Bersih**, **OOP (Encapsulation, Inheritance, Polymorphism, Abstraction)**, **PDO Strict Mode**, **Keamanan Sesi & Anti-XSS**, serta antarmuka modern responsif **HTML5 Semantik, CSS3 Grid/Flexbox, dan Vanilla JS**.

---

## 📸 Tampilan Antarmuka & Kesesuaian Desain (Desktop & Mobile)

Aplikasi dirancang presisi sesuai wireframe dan standar visual logistik:
1. **Desktop Split-Screen & Mobile Login**:
   - Desktop: Banner gelap elegan (`#0d282c`) di sisi kiri berisi headline heroik, widget *"Cek resi tanpa login"* instan, dan preview live checkpoint. Sisi kanan formulir login petugas.
   - Mobile: Tampilan vertikal adaptif dengan kartu brand KargoTrack, form login petugas cabang, dan tombol lacak cepat warna emas (`#f59e0b`).
2. **Wilayah, Rute & Kalkulator Tarif**:
   - Tab navigasi antara *Rute & Tarif* dan *Wilayah Operasional*.
   - Tabel responsif lengkap dengan badge status (*Aktif*, *Terbatas*, *Nonaktif*), tarif per kg, dan estimasi waktu tempuh.
   - **Kalkulator Tarif Dark Teal Card**: Hitung otomatis berat volume standar kargo darat via Vanilla JS:
     $$\text{Berat Volume (kg)} = \frac{P \times L \times T}{6000} \times \text{Jumlah Koli}$$
     Perbandingan otomatis *Chargeable Weight* (terbesar antara aktual vs volume). Tombol **"Buat resi dari tarif ini"** memindahkan data kalkulasi langsung ke formulir penerbitan resi.
3. **Penerbitan Resi & Pratinjau Struk (Thermal 58 mm & A4)**:
   - Formulir terstruktur: *Data pengirim & penerima* dan *Paket kiriman*.
   - **Validasi Data Penerima Real-Time**:
     - Nomor telepon: Wajib format Indonesia (mulai `08`, 10–13 digit). Border merah & pesan error: *"Nomor tidak lengkap. Isi 10-13 digit, mulai 08."*
     - Kode pos: Wajib tepat 5 digit angka. Border merah & pesan error: *"Kode pos harus 5 digit."*
   - **Live Receipt Preview**: Pratinjau interaktif struk kasir/gudang dengan tab switch **Thermal 58 mm** dan **A4 (Surat Muatan Kargo 3 Rangkap)**, dilengkapi barcode CODE128 scannable via JsBarcode dan tombol cetak langsung.
4. **Tracking Checkpoint & Status Serah Terima (POD)**:
   - **Visual Timeline CSS**: Garis kronologis dinamis dengan indikator ring ganda (selesai), glowing amber ring berdenyut (checkpoint aktif), dan lingkaran abu-abu (tahapan mendatang).
   - **Upload Bukti Serah Terima (POD)**: Area drag-and-drop file foto serah terima (JPG/PNG maks 5 MB) dengan visual chips upload (`[foto_1.jpg] [ttd.png] 2 file siap diunggah`).
   - **Digital Canvas Signature Pad**: Kanvas goresan tanda tangan digital touchscreen HP maupun mouse desktop.
5. **Manifest Muatan Armada Truk Antar-Kota**:
   - Kelompokkan puluhan resi gudang ke dalam armada truk kargo (No. Manifest e.g. `MF-0231`, No. Polisi e.g. `B 9127 KCD`, Sopir, Asal, Tujuan).
   - Aksi **"Berangkatkan Truk"** secara otomatis mengupdate status semua resi muatan menjadi *"Dalam perjalanan"* dan mencatat tracking checkpoint baru.

---

## 🏛️ Penerapan 4 Pilar OOP (Object-Oriented Programming)

Aplikasi dirancang menggunakan prinsip OOP murni agar kode terstruktur, modular, dan mudah dikembangkan:

### 1. Encapsulation (Enkapsulasi)
- Properti sensitif database, konfigurasi koneksi PDO, dan state internal class dideklarasikan sebagai `private` atau `protected`.
- Pengaksesan dan manipulasi data dikontrol melalui method spesifik (misal: `Database::getInstance()`, `User::verifyCredentials()`, `Resi::generateNomorResi()`, `Session::setUser()`).
- Data sensitif seperti hash password disaring sebelum disimpan ke sesi pengguna.

### 2. Inheritance (Pewarisan)
- **Base Model (`Core\Model`)**: Mewariskan fungsionalitas CRUD database (`all`, `find`, `where`, `firstWhere`, `create`, `update`, `delete`, `count`, `query`) ke seluruh model entitas (`Wilayah`, `Rute`, `Resi`, `Tracking`, `Manifest`, `User`).
- **Base Controller (`Core\Controller`)**: Mewariskan helper standar (`view`, `json`, `redirect`, `requireAuth`, `validate`, `isAjax`, `verifyCsrf`, `setFlash`) ke seluruh controller modul.

### 3. Polymorphism (Polimorfisme)
- **Kalkulator Tarif Multilayanan (`Services\TarifCalculatorInterface`)**:
  - `RegulerTarifCalculator`: Pembagi volumetrik 6000, pengali standar 1.0x, minimal 1.0 kg.
  - `ExpressTarifCalculator`: Pembagi volumetrik 5000, pengali prioritas 1.35x, minimal 2.0 kg.
  - `TruckingTarifCalculator`: Pembagi volumetrik 6000, skala diskon volume tonase (10% jika $\ge 50$ kg, 15% jika $\ge 100$ kg).
  - Diproduksi secara polimorfis melalui `TarifCalculatorFactory::create($layanan)`.
- **Pencetakan Resi Multi-Format (`Services\ReceiptRendererInterface`)**:
  - `ThermalReceiptRenderer`: Merender dokumen format struk roll 58 mm ringkas untuk printer kasir kargo.
  - `A4ReceiptRenderer`: Merender format formal Surat Muatan Kargo (Airway Bill) 3 rangkap (Shipper, Cargo Hub, Consignee POD).
  - Diproduksi secara polimorfis melalui `ReceiptRendererFactory::create($format)`.

### 4. Abstraction (Abstraksi)
- Menggunakan `interface` dan `abstract class` (`TarifCalculatorInterface`, `ReceiptRendererInterface`, `Core\Model`, `Core\Controller`) untuk menyembunyikan detail implementasi query SQL dan algoritma penghitungan di balik kontrak method publik yang bersih.

---

## 🛡️ Standar Industri PHP Native & Keamanan

- **Pola MVC Bersih**:
  - `core/`: Router (`App.php`), Database Singleton (`Database.php`), Base Model & Controller, Session Manager, View Engine, Helpers.
  - `models/`: Entitas data (`Wilayah`, `Rute`, `Resi`, `Tracking`, `Manifest`, `User`) dan Service (`services/`).
  - `controllers/`: Controller penangan HTTP request dan respon JSON API.
  - `views/`: Layout semantik HTML5 dan template tampilan modular.
  - `public/`: Front controller `index.php`, aset CSS, JS, dan direktori file upload `uploads/pod/`.
- **Koneksi Database PDO Strict Mode**:
  - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
  - `PDO::ATTR_EMULATE_PREPARES => false`
  - `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`
  - 100% menggunakan *Prepared Statements* dengan parameter binding untuk mencegah SQL Injection.
- **Keamanan Otentikasi & Sesi**:
  - Hash kata sandi dengan algoritma `password_hash($password, PASSWORD_BCRYPT)`.
  - Verifikasi kata sandi dengan `password_verify($password, $hash)`.
  - Regenerasi ID sesi dengan `session_regenerate_id(true)` saat login sukses untuk mencegah *Session Fixation*.
  - Proteksi CSRF Token pada pengiriman formulir POST.
- **Sanitasi Output Anti-XSS**:
  - Seluruh output dinamis dilindungi dengan `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`.
  - Disediakan helper global `e($data)` untuk rendering aman di seluruh template view.

---

## 📂 Struktur Direktori Proyek

```
eks_kargo_tracking_system_revisi/
├── core/                               # Kerangka Inti MVC
│   ├── App.php                         # Router & Request Dispatcher
│   ├── Autoloader.php                  # PSR-4 Class Autoloader
│   ├── Controller.php                  # Abstract Base Controller
│   ├── Database.php                    # Singleton PDO Strict Mode
│   ├── helpers.php                     # Global Anti-XSS & Formatting Helpers
│   ├── Model.php                       # Abstract Base Model CRUD
│   ├── Security.php                    # Anti-XSS & CSRF Handler
│   ├── Session.php                     # Secure Session & Auth Manager
│   └── View.php                        # View Template Renderer Engine
├── models/                             # Domain Models (OOP Inheritance)
│   ├── Manifest.php                    # Model Manifest Truk Muatan
│   ├── Resi.php                        # Model Resi & Paket Kiriman
│   ├── Rute.php                        # Model Rute & Tarif
│   ├── Tracking.php                    # Model Checkpoint Perjalanan & POD
│   ├── User.php                        # Model Pengguna & Otentikasi
│   ├── Wilayah.php                     # Model Master Wilayah Operasional
│   └── services/                       # OOP Services (Polymorphism & Abstraction)
│       ├── A4ReceiptRenderer.php       # Renderer Resi Surat Muatan Kargo A4
│       ├── ExpressTarifCalculator.php  # Kalkulator Tarif Express
│       ├── ReceiptRendererFactory.php  # Factory Renderer Resi
│       ├── ReceiptRendererInterface.php# Interface Cetak Resi
│       ├── RegulerTarifCalculator.php  # Kalkulator Tarif Reguler
│       ├── TarifCalculatorFactory.php  # Factory Kalkulator Tarif
│       ├── TarifCalculatorInterface.php# Interface Kalkulator Tarif
│       └── TruckingTarifCalculator.php # Kalkulator Tarif Trucking FTL
├── controllers/                        # Controllers (MVC Dispatcher)
│   ├── AuthController.php              # Login, Logout & Session Guard
│   ├── KalkulatorController.php        # JSON API Kalkulator Tarif
│   ├── ManifestController.php          # CRUD Manifest & Keberangkatan Truk
│   ├── ResiController.php              # CRUD Resi & Cetak Thermal/A4
│   ├── RuteController.php              # CRUD Rute & Live Search
│   ├── TrackingController.php          # CRUD Checkpoint & Upload POD
│   └── WilayahController.php           # CRUD Wilayah & Live Search
├── views/                              # Views (HTML5 Semantik)
│   ├── auth/
│   │   └── login.php                   # Split Screen Desktop & Mobile Login
│   ├── layouts/
│   │   ├── bottom_nav.php              # Mobile Fixed Bottom Navigation Bar
│   │   ├── main.php                    # Main HTML5 Semantic Layout
│   │   └── sidebar.php                 # Desktop Dark Sidebar (#0d282c)
│   ├── manifest/
│   │   └── index.php                   # Daftar Manifest & Muatan Truk
│   ├── print/
│   │   └── view.php                    # Halaman Cetak Resi Thermal/A4
│   ├── resi/
│   │   └── index.php                   # Form Penerbitan Resi & Live Preview
│   ├── tracking/
│   │   ├── index.php                   # Timeline Visual & Tambah Checkpoint
│   │   └── public.php                  # Portal Lacak Resi Publik Tanpa Login
│   └── wilayah/
│       └── index.php                   # Tab Wilayah, Rute & Kalkulator Tarif
├── public/                             # Public Web Root
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css               # CSS3 Variables, Grid/Flexbox, Print
│   │   └── js/
│   │       ├── app.js                  # Vanilla JS Interaktivitas
│   │       └── jsbarcode.min.js        # Barcode CODE128 Generator
│   ├── uploads/
│   │   └── pod/                        # Direktori Foto POD & TTD Digital
│   ├── .htaccess                       # URL Rewriting ke index.php
│   └── index.php                       # Front Controller Utama
├── database/
│   ├── schema.sql                      # Skema Lengkap MySQL / MariaDB
│   └── seed.php                        # Database Auto-Seeder
├── .htaccess                           # Root Forwarder ke public/
├── index.php                           # Root Entrypoint
└── README.md                           # Dokumentasi Lengkap Proyek
```

---

## 🚀 Panduan Menjalankan Aplikasi

Aplikasi telah terintegrasi di web server Laragon:

### Opsi 1: Menggunakan Laragon (Sangat Disarankan)
1. Buka aplikasi **Laragon**.
2. Pastikan service **Apache** dan **MySQL** aktif (*Start All*).
3. Buka browser dan kunjungi:
   ```
   http://localhost/eks_kargo_tracking_system_revisi/
   ```

### Opsi 2: Menggunakan PHP Built-in Server
Jika ingin menjalankan secara standalone di terminal:
```powershell
& "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" -S localhost:8000 -t public
```
Lalu buka: `http://localhost:8000/`

---

## 🔑 Kredensial Login & Data Uji Coba

- **Email Petugas**: `rizqi@kargotrack.id`
- **Kata Sandi**: `admin123`
- **Nama Petugas**: Rizqi (Admin Gudang Cabang Cirebon)

### Sampel Resi & Nomor Uji Coba:
1. `KT-CRB-261030-0047`:
   - Rute: Cirebon &rarr; Jakarta (Dewi Lestari)
   - Status: Dalam perjalanan (Tiba di hub Bekasi, Transit sortir)
   - Isi: Pupuk organik 2 koli (14 kg aktual, 40 kg volume, Ongkir Rp 180.000)
2. `KT-CRB-261029-0012`:
   - Rute: Cirebon &rarr; Bandung (Bambang Wijaya)
   - Status: Diterima penerima (Lengkap dengan bukti foto POD dan tanda tangan digital)
3. `KT-CRB-261030-0089`:
   - Rute: Cirebon &rarr; Surabaya (Ibu Nurul Hidayah)
   - Status: Paket diterima gudang (Sentra Batik Trusmi)

---

Dibuat dengan standar kode tinggi, modular, bersih, dan sesuai dengan seluruh spesifikasi studi kasus ekspedisi kargo logistik antar-kota.

