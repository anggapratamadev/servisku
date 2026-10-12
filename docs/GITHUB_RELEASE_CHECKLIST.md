# GitHub Release Checklist

Checklist ini ditujukan untuk rilis kode sumber ServisKu sebagai portofolio GitHub, bukan sebagai sertifikasi keamanan atau bukti bahwa aplikasi siap menerima transaksi nyata.

## Sebelum commit

- [ ] Pastikan `config/google.local.php` tidak ada di daftar file yang akan di-commit.
- [ ] Pastikan `.env` dan file backup/log lokal tidak ikut di-commit.
- [ ] Pastikan tidak ada password database, OAuth Client Secret, token, atau data pribadi nyata di kode, SQL, screenshot, dan dokumentasi.
- [ ] Periksa isi staged changes dengan `git status` dan `git diff --cached`.
- [ ] Pastikan `.gitignore` berada di root repository.
- [ ] Jangan mengunggah folder `.git` dari arsip proyek lain atau file ZIP hasil build ke repository kecuali memang sengaja diperlukan.

## Pemeriksaan lokal

- [ ] Jalankan pemeriksaan sintaks PHP pada semua file.
- [ ] Import `database/servisku.sql` hanya ke database kosong.
- [ ] Uji login tiap role dan alur booking sampai verifikasi pembayaran demo.
- [ ] Uji akses tidak sah, CSRF, ownership data, dan upload bukti pembayaran.
- [ ] Uji Google Login hanya dengan kredensial privat dan callback yang sesuai.
- [ ] Baca `docs/SECURITY_AUDIT.md` dan `docs/DEPLOYMENT.md`.

## Saat push

1. Buat repository GitHub dengan nama `servisku`.
2. Tinjau file yang akan dikirim sebelum commit pertama.
3. Push hanya source code, dokumentasi, SQL installer demo, asset yang boleh dibagikan, dan file contoh konfigurasi.
4. Jangan menganggap menghapus secret setelah commit cukup. Jika secret pernah di-commit, rotasi/revoke terlebih dahulu; riwayat Git dapat tetap menyimpan nilai lama.

## Sesudah push

- [ ] Buka repository melalui browser dan cek README tampil baik.
- [ ] Pastikan `.env`, `config/google.local.php`, dan bukti pembayaran pribadi tidak muncul.
- [ ] Uji clone bersih dan ikuti petunjuk instalasi README.
- [ ] Tambahkan screenshot demo yang sudah disanitasi bila diperlukan.
- [ ] Tambahkan `LICENSE` jika ingin menetapkan lisensi distribusi.
