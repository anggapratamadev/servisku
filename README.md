# ServisKu — Vehicle Service Management & Booking System

ServisKu adalah aplikasi web manajemen servis kendaraan berbasis **PHP Native dan MySQL/MariaDB**. Aplikasi ini dibuat sebagai proyek portofolio untuk memperlihatkan alur pemesanan servis dari pelanggan, pengelolaan bengkel oleh admin, hingga pengerjaan oleh mekanik.

> **Status proyek:** pemeriksaan sintaks PHP telah dilakukan. Pengujian menyeluruh pada browser dan database tetap perlu dijalankan di lingkungan lokal sebelum fitur dianggap terverifikasi.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Peran Pengguna](#peran-pengguna)
- [Alur Servis](#alur-servis)
- [Struktur Folder](#struktur-folder)
- [Persyaratan](#persyaratan)
- [Instalasi di Laragon](#instalasi-di-laragon)
- [Akun Demo Lokal](#akun-demo-lokal)
- [Konfigurasi Environment dan Google Login](#konfigurasi-environment-dan-google-login)
- [API](#api)
- [Database dan Migrasi](#database-dan-migrasi)
- [Keamanan dan Batasan](#keamanan-dan-batasan)
- [Checklist Pengujian](#checklist-pengujian)
- [Dokumentasi Tambahan](#dokumentasi-tambahan)

## Fitur Utama

- Registrasi, login, logout, session, dan pembatasan akses berdasarkan role.
- Login Google OAuth 2.0 opsional untuk pelanggan.
- Pengelolaan kendaraan dan pemesanan servis.
- Dashboard admin untuk pelanggan, kendaraan, layanan, mekanik, booking, sparepart, pembayaran, dan laporan.
- Dashboard mekanik untuk melihat pekerjaan, mengisi pemeriksaan/diagnosis, mencatat sparepart, dan memperbarui progres servis.
- Riwayat servis, invoice, notifikasi, dan activity log.
- Pengiriman bukti pembayaran serta verifikasi manual oleh admin.
- QR tracking untuk melihat informasi status booking yang disediakan aplikasi.
- Endpoint API berbasis JSON untuk health check dan informasi booking dengan pemeriksaan akses.
- Tampilan responsif untuk desktop dan perangkat mobile.

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | PHP Native (gunakan PHP 8.x yang kompatibel dengan lingkunganmu) |
| Database | MySQL atau MariaDB |
| Akses database | PDO MySQL |
| Frontend | HTML, CSS, JavaScript |
| Server lokal | Laragon / Apache |
| Login eksternal opsional | Google OAuth 2.0 |

## Peran Pengguna

| Role | Tanggung jawab |
|---|---|
| **Pelanggan** | Mengelola kendaraan, membuat booking, melihat progres, invoice, pembayaran, dan riwayat. |
| **Admin** | Mengelola data operasional bengkel, mengonfirmasi booking, menugaskan mekanik, memverifikasi pembayaran, dan melihat laporan. |
| **Mekanik** | Melihat pekerjaan yang ditugaskan, mencatat pemeriksaan dan diagnosis, mengelola catatan sparepart, serta memperbarui progres pekerjaan. |

## Alur Servis

```text
Pelanggan daftar/login
        ↓
Tambah kendaraan dan buat booking
        ↓
Admin konfirmasi booking dan menetapkan mekanik
        ↓
Mekanik memeriksa kendaraan dan mengerjakan servis
        ↓
Invoice dibuat / status menunggu pembayaran
        ↓
Pelanggan mengirim detail pembayaran atau bukti demo
        ↓
Admin memeriksa dan menyetujui/menolak pembayaran
        ↓
Riwayat servis dan laporan diperbarui
```

## Struktur Folder

```text
servisku/
├── admin/                   # Halaman dan pengelolaan oleh admin
├── api/                     # Endpoint JSON
├── assets/                  # CSS, JavaScript, dan gambar
├── auth/                    # Login, register, logout, Google OAuth
├── config/                  # Koneksi database dan loader konfigurasi OAuth
├── customer/                # Halaman pelanggan, kendaraan, booking, pembayaran
├── database/
│   └── servisku.sql         # Satu-satunya file SQL untuk instalasi database baru
├── docs/                    # Panduan, ERD, API, audit, dan checklist
├── includes/                # Helper, session, role, CSRF, layout bersama
├── mechanic/                # Halaman mekanik
├── uploads/                 # Lokasi penyimpanan bukti pembayaran lokal
├── .env.example             # Contoh nama variabel environment (bukan file rahasia)
├── .gitignore               # Aturan file yang tidak boleh di-commit
├── index.php                # Halaman awal
└── tracking.php             # Halaman tracking booking
```

## Persyaratan

- Laragon atau lingkungan Apache + PHP + MySQL/MariaDB.
- PHP 8.x dan ekstensi PDO MySQL. Ekstensi tambahan seperti cURL dan Fileinfo mungkin dibutuhkan oleh fitur tertentu.
- Browser modern.
- Git hanya diperlukan jika ingin meng-clone atau mengirim proyek ke GitHub.

## Instalasi di Laragon

1. Unduh ZIP proyek atau clone repositori ke `C:\laragon\www\servisku`.
2. Buka Laragon, lalu jalankan **Apache** dan **MySQL**.
3. Buka phpMyAdmin atau tool database yang kamu gunakan, lalu buat database bernama `servisku` (collation yang disarankan `utf8mb4_unicode_ci`).
4. Pilih database yang baru dibuat dan impor file `database/servisku.sql`.
5. Periksa `config/database.php`. Untuk default Laragon, konfigurasi lokal menggunakan host `127.0.0.1`, database `servisku`, user `root`, dan password kosong. Sesuaikan bila konfigurasi MySQL-mu berbeda.
6. Buka `http://localhost/servisku/`.
7. Login menggunakan salah satu akun demo di bawah, lalu coba alur booking.
8. Google Login bersifat opsional. Untuk mengaktifkannya, ikuti bagian [Konfigurasi Environment dan Google Login](#konfigurasi-environment-dan-google-login).

### Peringatan impor database

`database/servisku.sql` adalah **satu-satunya file SQL yang disediakan** dan hanya untuk instalasi baru pada database kosong. Script membuat tabel dan menjalankan `DROP TABLE IF EXISTS`; mengimpornya ke database yang sudah berisi data dapat menghapus tabel/data terkait. Jangan impor ulang ke database aktif dan jangan gunakan sebagai skrip upgrade. Backup data terlebih dahulu. Untuk database lama, lakukan backup dan migrasi skema secara terencana sebelum mengubahnya.

## Akun Demo Lokal

Akun berikut disediakan untuk pengujian lokal setelah database installer diimpor.

| Role | Email / username | Password |
|---|---|---|
| Admin | `admin@servisku.local` (username `admin` juga dapat digunakan bila form mendukungnya) | `admin123` |
| Pelanggan | `customer@servisku.local` | `customer123` |
| Mekanik | `mechanic@servisku.local` | `mechanic123` |

**Penting:** akun tersebut hanya untuk demo lokal. Jangan memakai password ini pada deployment publik. Untuk demonstrasi publik, ubah atau hapus akun demo dan jangan menyertakan data pribadi pelanggan sungguhan.

## Konfigurasi Environment dan Google Login

### Perbedaan `.env.example` dan `config/google.example.php`

Kedua file ini punya fungsi berbeda, jadi tidak saling menggantikan:

- **`.env.example`** adalah daftar contoh variabel konfigurasi umum, seperti database dan environment. Pada versi ini file tersebut hanya dokumentasi; PHP tidak memuat `.env` secara otomatis.
- **`config/google.example.php`** adalah contoh konfigurasi khusus Google OAuth dalam format PHP. Untuk mengaktifkan Google Login lokal, salin file ini menjadi `config/google.local.php`, lalu isi Client ID dan Client Secret milik sendiri. File `google.local.php` tidak boleh di-commit.

Karena fungsinya berbeda, keduanya dipertahankan. Jangan memasukkan kredensial asli ke kedua file contoh.

- Di Laragon, konfigurasi database lokal default ada di `config/database.php`.
- Di server, atur variabel `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `APP_ENV`, `APP_DEBUG`, dan bila diperlukan `APP_BASE_URL` melalui konfigurasi environment/server.
- Jangan mengganti nama `.env.example` menjadi `.env` dengan asumsi aplikasi akan membacanya. Dukungan loader `.env` tidak dipasang pada versi ini.
- Jangan pernah commit `.env`, `config/google.local.php`, password database, atau secret OAuth.

### Mengaktifkan Google Login secara lokal

1. Buka Google Cloud Console dan pilih/buat project.
2. Buat atau pilih OAuth Client ID bertipe **Web application**.
3. Tambahkan Authorized JavaScript origin `http://localhost` jika diminta.
4. Tambahkan Authorized redirect URI berikut, persis sama:

   `http://localhost/servisku/auth/google_callback.php`

5. Salin `config/google.example.php` menjadi `config/google.local.php`.
6. Masukkan Client ID dan Client Secret milikmu ke `config/google.local.php`. File `google.local.php` sudah masuk `.gitignore`.
7. Buka ulang halaman login dan tekan `Ctrl + F5`, lalu uji tombol Google.

Jangan kirim Client Secret melalui chat, jangan memasukkannya ke screenshot, dan jangan commit file `google.local.php`. Jika secret pernah terunggah atau dibagikan, buat/rotasi secret baru di Google Cloud Console.

Untuk server sungguhan, redirect URI harus menggunakan domain HTTPS milikmu dan harus sama persis dengan konfigurasi Google. Panduan tambahan ada di `docs/DEPLOYMENT.md`.

## API

Dokumentasi endpoint ada di [`docs/API.md`](docs/API.md). Endpoint yang didokumentasikan mencakup:

- `GET /servisku/api/health.php` — pemeriksaan status dasar aplikasi.
- `GET /servisku/api/bookings.php` — daftar booking sesuai aturan akses endpoint.
- `GET /servisku/api/booking.php?id=<booking_id>` — detail booking sesuai aturan akses endpoint.

Uji endpoint menggunakan sesi login yang sesuai. Jangan menganggap endpoint publik atau membagikan data booking pengguna lain. Detail respons dan autentikasi perlu dilihat di dokumentasi API dan kode endpoint.

## Database dan Migrasi

- `database/servisku.sql`: satu-satunya installer untuk database baru yang masih kosong. File ini sudah mencakup tabel activity log dan notifikasi.
- Tidak ada file migrasi terpisah dalam paket ini agar instalasi baru lebih sederhana. Jangan jalankan installer ini terhadap database lama yang menyimpan data.
- Aplikasi tidak menjalankan migrasi otomatis setiap kali halaman dibuka.
- Struktur relasi tersedia di [`docs/ERD.md`](docs/ERD.md).

## Keamanan dan Batasan

- Password demo disimpan dalam bentuk hash pada SQL installer.
- Koneksi database menggunakan PDO dan prepared statements di query yang menggunakannya.
- Ada pemeriksaan role, token CSRF, dan perlindungan folder tertentu; tetap lakukan pengujian akses untuk setiap fitur.
- Folder bukti pembayaran perlu diuji pada server aktual. File `.htaccess` melindungi server Apache yang mengizinkannya; konfigurasi Nginx atau server lain memerlukan aturan setara.
- Pembayaran merupakan **alur verifikasi manual/demo**, bukan payment gateway sungguhan. Jangan menganggap status pembayaran otomatis terhubung ke bank atau penyedia QRIS.
- Gambar QRIS dan detail pembayaran demo tidak boleh digunakan untuk menerima pembayaran sungguhan tanpa diganti dan diverifikasi.
- Fitur QR tracking menggunakan layanan QR eksternal sesuai catatan di `docs/DEPLOYMENT.md`; pertimbangkan implikasi privasi sebelum penggunaan publik.
- Static review bukan penetration test. Pengujian runtime semua alur dan review otorisasi tetap diperlukan.

## Checklist Pengujian

Gunakan checklist berikut setelah instalasi database baru:

- [ ] Halaman awal dan login terbuka tanpa error.
- [ ] Login, register, logout, dan pembatasan role bekerja.
- [ ] Pelanggan dapat menambah/mengubah kendaraan.
- [ ] Booking tidak tercatat ganda dan status booking berubah sesuai alur.
- [ ] Admin dapat mengonfirmasi booking dan menetapkan mekanik.
- [ ] Mekanik dapat mencatat pemeriksaan, diagnosis, sparepart, dan progres.
- [ ] Invoice menghitung total dengan benar.
- [ ] Pengiriman bukti pembayaran, penolakan, pengiriman ulang, dan persetujuan dapat diuji.
- [ ] Pelanggan tidak dapat melihat data pelanggan lain.
- [ ] Activity log, notifikasi, laporan, dan tracking dapat dibuka.
- [ ] API hanya mengembalikan data yang boleh diakses oleh sesi tersebut.
- [ ] Upload bukti pembayaran memvalidasi file dan tidak dapat mengeksekusi script.
- [ ] Layout diuji pada desktop dan mobile.
- [ ] Google Login diuji bila diaktifkan.

## Dokumentasi Tambahan

- [`docs/REQUIREMENTS.md`](docs/REQUIREMENTS.md) — kebutuhan aplikasi.
- [`docs/ERD.md`](docs/ERD.md) — relasi database.
- [`docs/API.md`](docs/API.md) — dokumentasi API.
- [`docs/DEMO.md`](docs/DEMO.md) — alur demo portofolio.
- [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) — konfigurasi dan catatan deployment.
- [`docs/SECURITY_AUDIT.md`](docs/SECURITY_AUDIT.md) — cakupan review dan risiko yang belum diverifikasi.
- [`docs/RELEASE_CHECKLIST.md`](docs/RELEASE_CHECKLIST.md) — checklist rilis aplikasi.
- [`docs/GITHUB_RELEASE_CHECKLIST.md`](docs/GITHUB_RELEASE_CHECKLIST.md) — checklist sebelum commit dan push ke GitHub.

## Lisensi

Belum ada lisensi open-source yang ditentukan. Jika ingin mengizinkan orang lain menggunakan, memodifikasi, atau mendistribusikan proyek secara resmi, tambahkan file `LICENSE` dengan lisensi yang kamu pilih.

---

**ServisKu** — Vehicle Service Management & Booking System
