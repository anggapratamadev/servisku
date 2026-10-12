# ServisKu JSON API

ServisKu menyediakan API JSON read-only untuk kebutuhan portfolio, integrasi dashboard, atau aplikasi mobile sederhana.

## Base URL

```text
http://localhost/servisku/api/
```

## Authentication

Endpoint booking menggunakan **session login ServisKu**. User harus login terlebih dahulu pada browser yang sama.

- Customer: hanya dapat melihat booking miliknya.
- Mechanic: hanya dapat melihat booking yang ditugaskan kepadanya.
- Admin: dapat melihat seluruh booking.

API ini sengaja read-only pada v1. Tidak ada endpoint POST/PUT/DELETE untuk mengubah transaksi.

## Endpoint

### Health check

`GET /api/health.php`

Contoh response:

```json
{
  "success": true,
  "service": "ServisKu API",
  "version": "1.0",
  "status": "ok"
}
```

### Daftar booking

`GET /api/bookings.php`

Mengembalikan booking sesuai hak akses user yang sedang login.

### Detail booking

`GET /api/booking.php?id=123`

Mengembalikan detail booking, kendaraan, layanan, mekanik, service record, sparepart, total biaya, dan status pembayaran.

## HTTP status

- `200` berhasil
- `400` parameter tidak valid
- `401` belum login
- `404` data tidak ditemukan/tidak boleh diakses
- `405` method tidak diizinkan
- `500` kesalahan server

## Catatan keamanan

API tidak mengembalikan password, token OAuth, file bukti pembayaran, atau secret konfigurasi. Data customer dibatasi berdasarkan role dan kepemilikan booking.
