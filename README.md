# ServisKu — Vehicle Service Management & Booking System

Aplikasi web untuk membantu bengkel mengelola pemesanan dan proses servis kendaraan dalam satu sistem. ServisKu dibuat sebagai proyek portofolio menggunakan PHP Native dan MySQL/MariaDB, dengan alur kerja untuk pelanggan, admin bengkel, dan mekanik.

<p align="center">
  <strong>PHP Native · MySQL/MariaDB · HTML · CSS · JavaScript</strong>
</p>

## Ringkasan

ServisKu menghubungkan proses pemesanan servis oleh pelanggan dengan pengelolaan booking oleh admin dan pembaruan pekerjaan oleh mekanik. Aplikasi juga menyediakan pencatatan pembayaran dan riwayat servis.

> **Status:** dokumentasi dan pemeriksaan sintaks PHP tersedia. Pengujian menyeluruh pada browser, database, dan seluruh alur bisnis tetap perlu dilakukan di lingkungan lokal. Jangan menganggap semua fitur telah lolos pengujian hanya berdasarkan pemeriksaan sintaks.

## Fitur Utama

- Registrasi, login, logout, session, dan pembatasan akses berdasarkan role.
- Login Google OAuth 2.0 opsional untuk pelanggan.
- Pengelolaan kendaraan dan pemesanan servis.
- Dashboard admin untuk mengelola data operasional bengkel, booking, mekanik, sparepart, pembayaran, dan laporan.
- Dashboard mekanik untuk melihat pekerjaan yang ditugaskan, mencatat pemeriksaan/diagnosis, sparepart, dan progres servis.
- Riwayat servis, invoice, notifikasi, dan activity log.
- Pengiriman bukti pembayaran dan verifikasi manual oleh admin.
- QR tracking untuk melihat informasi status booking.
- Endpoint API berbasis JSON untuk health check dan informasi booking dengan pemeriksaan akses.
- Tampilan responsif untuk desktop dan perangkat mobile.

## Peran Pengguna

| Peran | Tanggung jawab |
|---|---|
| **Pelanggan** | Mengelola kendaraan, membuat booking, melihat progres servis, invoice, pembayaran, dan riwayat. |
| **Admin** | Mengelola data bengkel, mengonfirmasi booking, menetapkan mekanik, memverifikasi pembayaran, dan melihat laporan. |
| **Mekanik** | Melihat pekerjaan yang ditugaskan, mencatat pemeriksaan dan diagnosis, mencatat sparepart, serta memperbarui progres servis. |

## Alur Servis

```text
Pelanggan daftar / login
        ↓
Menambahkan kendaraan dan membuat booking
        ↓
Admin mengonfirmasi booking dan menetapkan mekanik
        ↓
Mekanik memeriksa kendaraan dan mengerjakan servis
        ↓
Invoice dibuat dan pembayaran diproses
        ↓
Pelanggan mengirim detail atau bukti pembayaran
        ↓
Admin memeriksa pembayaran
        ↓
Status dan riwayat servis diperbarui
```

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | PHP Native |
| Database | MySQL / MariaDB |
| Akses database | PDO MySQL |
| Frontend | HTML, CSS, JavaScript |
| Server lokal | Laragon / Apache |
| Login eksternal opsional | Google OAuth 2.0 |

## Persyaratan

- Laragon atau lingkungan Apache + PHP + MySQL/MariaDB.
- PHP 8.x yang kompatibel dengan kode proyek.
- Ekstensi PDO MySQL; ekstensi tambahan seperti cURL dan Fileinfo mungkin dibutuhkan oleh fitur tertentu.
- Browser modern.
- Git, jika ingin melakukan clone atau mengirim perubahan ke GitHub.

## Instalasi Lokal (Laragon)

1. Clone repository atau letakkan proyek pada folder `C:\laragon\www\servisku`.
2. Jalankan **Apache** dan **MySQL** melalui Laragon.
3. Buat database baru bernama `servisku` melalui phpMyAdmin atau tool database lain.
4. Pilih database tersebut, lalu impor `database/servisku.sql`.
5. Periksa `config/database.php`. Konfigurasi default Laragon menggunakan host `127.0.0.1`, database `servisku`, user `root`, dan password kosong. Sesuaikan jika konfigurasi lokal berbeda.
6. Buka [http://localhost/servisku/](http://localhost/servisku/).
7. Login dengan akun demo lokal di bawah. Untuk urutan demonstrasi fitur, lihat [`docs/DEMO.md`](docs/DEMO.md).

### Perhatian sebelum impor database

`database/servisku.sql` adalah installer untuk **database baru yang masih kosong**. Script berisi perintah `DROP TABLE IF EXISTS`, sehingga impor ke database yang sudah berisi data dapat menghapus tabel/data terkait. Jangan gunakan file ini sebagai skrip upgrade dan selalu buat backup sebelum mengubah database yang berisi data.

## Akun Demo Lokal

Akun berikut disediakan untuk pengujian lokal setelah installer database diimpor.

| Peran | Email / username | Password |
|---|---|---|
| Admin | `admin` atau `admin@servisku.local` | `admin123` |
| Pelanggan | `customer@servisku.local` | `customer123` |
| Mekanik | `mechanic@servisku.local` | `mechanic123` |

**Penting:** akun ini hanya untuk demo lokal. Jangan gunakan password tersebut di deployment publik. Untuk demonstrasi publik, ganti atau hapus akun demo dan jangan gunakan data pribadi pelanggan sungguhan.

## Konfigurasi Google Login (Opsional)

Google Login membutuhkan konfigurasi OAuth milik sendiri.

1. Di Google Cloud Console, buat atau pilih project dan OAuth Client ID bertipe **Web application**.
2. Tambahkan redirect URI berikut, persis sama:

   `http://localhost/servisku/auth/google_callback.php`

3. Salin `config/google.example.php` menjadi `config/google.local.php`.
4. Isi Client ID dan Client Secret milik sendiri pada `config/google.local.php`.
5. Pastikan file lokal tersebut tidak masuk ke commit. File `config/google.local.php` sudah dicantumkan di `.gitignore`.

`.env.example` adalah contoh dokumentasi variabel environment umum. Pada versi ini, file tersebut **tidak dimuat otomatis** oleh PHP. Konfigurasi database lokal default berada di `config/database.php`; periksa kode dan dokumentasi deployment sebelum mengubah konfigurasi server.

Jangan pernah commit `.env`, `config/google.local.php`, password database, atau OAuth secret. Jika secret pernah terunggah atau dibagikan, rotasi secret melalui Google Cloud Console.

Untuk penggunaan pada server, gunakan domain HTTPS dan redirect URI yang sesuai. Lihat `docs/DEPLOYMENT.md` untuk catatan tambahan.

## API

Dokumentasi endpoint tersedia di [`docs/API.md`](docs/API.md). Endpoint yang didokumentasikan mencakup:

- `GET /servisku/api/health.php` — pemeriksaan status dasar aplikasi.
- `GET /servisku/api/bookings.php` — daftar booking sesuai aturan akses endpoint.
- `GET /servisku/api/booking.php?id=<booking_id>` — detail booking sesuai aturan akses endpoint.

Uji endpoint menggunakan sesi login yang sesuai. Jangan menganggap endpoint bersifat publik atau membagikan data booking pengguna lain.

## Database

- `database/servisku.sql` adalah installer untuk database baru.
- Tidak ada file migrasi terpisah dalam paket ini.
- Aplikasi tidak menjalankan migrasi otomatis setiap kali halaman dibuka.
- Relasi database didokumentasikan pada [`docs/ERD.md`](docs/ERD.md).

## Keamanan dan Batasan

- Password akun demo disimpan dalam bentuk hash pada SQL installer.
- Aplikasi menggunakan PDO dan prepared statements pada query yang menggunakannya.
- Terdapat pemeriksaan role, token CSRF, dan perlindungan folder tertentu; pengujian akses setiap fitur tetap diperlukan.
- Perlindungan `.htaccess` berlaku untuk server Apache yang mengizinkannya. Server lain memerlukan aturan setara.
- Pembayaran adalah alur verifikasi manual/demo, **bukan integrasi payment gateway sungguhan**.
- Gambar QRIS dan detail pembayaran demo tidak boleh digunakan untuk menerima pembayaran nyata tanpa diganti dan diverifikasi.
- Fitur QR tracking menggunakan layanan QR eksternal sesuai catatan pada `docs/DEPLOYMENT.md`; pertimbangkan implikasi privasi sebelum penggunaan publik.
- Pemeriksaan sintaks bukan penetration test. Pengujian runtime dan review otorisasi tetap diperlukan sebelum deployment.

## Checklist Pengujian

- [ ] Halaman awal dan login terbuka tanpa error.
- [ ] Login, registrasi, logout, dan pembatasan role berjalan.
- [ ] Pelanggan dapat menambah/mengubah kendaraan.
- [ ] Booking tidak tercatat ganda dan statusnya berubah sesuai alur.
- [ ] Admin dapat mengonfirmasi booking dan menetapkan mekanik.
- [ ] Mekanik dapat mencatat pemeriksaan, diagnosis, sparepart, dan progres.
- [ ] Invoice menghitung total dengan benar.
- [ ] Bukti pembayaran dapat dikirim, ditolak, dikirim ulang, dan disetujui.
- [ ] Pelanggan tidak dapat melihat data pelanggan lain.
- [ ] Activity log, notifikasi, laporan, dan tracking dapat dibuka.
- [ ] API hanya mengembalikan data yang boleh diakses oleh sesi terkait.
- [ ] Upload bukti pembayaran memvalidasi file dan mencegah eksekusi script.
- [ ] Tampilan diuji di desktop dan mobile.
- [ ] Google Login diuji jika diaktifkan.

## Dokumentasi

- [`docs/REQUIREMENTS.md`](docs/REQUIREMENTS.md) — kebutuhan aplikasi.
- [`docs/ERD.md`](docs/ERD.md) — relasi database.
- [`docs/API.md`](docs/API.md) — dokumentasi API.
- [`docs/DEMO.md`](docs/DEMO.md) — alur demo portofolio.
- [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) — konfigurasi dan deployment.
- [`docs/SECURITY_AUDIT.md`](docs/SECURITY_AUDIT.md) — cakupan review dan risiko yang belum diverifikasi.
- [`docs/RELEASE_CHECKLIST.md`](docs/RELEASE_CHECKLIST.md) — checklist rilis.
- [`docs/GITHUB_RELEASE_CHECKLIST.md`](docs/GITHUB_RELEASE_CHECKLIST.md) — checklist sebelum commit dan push.

## Lisensi

Repository ini belum menetapkan lisensi open-source. Tambahkan file `LICENSE` jika ingin mengatur secara resmi bagaimana orang lain boleh menggunakan, memodifikasi, atau mendistribusikan proyek.

---

**ServisKu** — Vehicle Service Management & Booking System
