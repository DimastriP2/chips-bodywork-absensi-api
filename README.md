# Chips Bodywork — Attendance API & Admin

Sistem absensi berbasis lokasi dengan API untuk aplikasi mobile dan dashboard admin.
Dikembangkan sebagai proyek portofolio Dimas Tri Pamungkas.

**Stack:** PHP 8.2+ · Laravel 12 · Sanctum 4 · Blade · Bootstrap 5 · Pest.
Workflow pengujian menggunakan PHP 8.3, Node 22, dan SQLite.

## Fitur

- Login token dengan kebijakan satu sesi mobile per akun, logout, dan ganti password.
- Check in/check out dengan validasi koordinat dan radius kantor di server.
- Satu absensi per karyawan per tanggal melalui transaksi, row lock, dan unique index.
- Status hari ini, ringkasan bulanan, serta riwayat dengan filter dan pagination opsional.
- Dashboard admin: jumlah karyawan, masuk/pulang, catatan terbaru, dan tren tujuh hari.
- Pengelolaan karyawan dan lokasi kantor; rekap serta ekspor CSV/PDF yang sudah tersedia.
- Data demo lokal, dokumentasi kontrak API, dan pengujian otomatis.

## Menjalankan secara lokal

Prasyarat: PHP beserta PDO SQLite, Composer, Node.js 22, dan npm.

```sh
git clone https://github.com/DimastriP2/chips-bodywork-absensi-api.git
cd chips-bodywork-absensi-api
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
npm ci
npm run build
php artisan db:seed --class=DemoSeeder
php artisan serve
```

Buka `http://127.0.0.1:8000`. Seeder menampilkan password acak untuk
`admin@example.test` dan tiga akun `staff1@example.test` sampai `staff3@example.test`.
Password hanya ditampilkan saat akun dibuat. Menjalankan ulang seeder tidak mereset
password atau menimpa absensi. Gunakan database lokal khusus demo.

Waktu bisnis memakai `Asia/Jakarta` sesuai konfigurasi proyek yang sudah ada.
Alamat API lokal: `http://127.0.0.1:8000/api`. Emulator Android biasanya mengakses host
melalui `http://10.0.2.2:8000/api`; perangkat fisik membutuhkan alamat host yang dapat
dijangkau perangkat.

## Skenario demonstrasi

1. Login admin untuk melihat tren dari data sintetis enam hari sebelumnya.
2. Login akun staff melalui API/mobile. Ambil titik demo dari `GET /api/office`.
3. Kirim check in di titik demo pada lingkungan pengujian. Dashboard menampilkan catatan baru setelah dimuat ulang.
4. Ulangi check in: API mengembalikan `409` dan mempertahankan waktu masuk pertama.
5. Coba check out di luar radius: ditolak `403`. Check out di dalam radius berhasil.
6. Tampilkan riwayat dan ringkasan bulanan; logout lalu pastikan token lama ditolak.

Koordinat demo adalah data sintetis. Ini bukan bukti lokasi kantor sebenarnya.
Hari ini sengaja tidak diisi seeder agar alur masuk/pulang dapat diperagakan langsung.

## Pengujian

```sh
php artisan test
php artisan view:cache
npm run build
```

Workflow `.github/workflows/tests.yml` menjalankan instalasi dari lockfile, build aset,
pemeriksaan sintaks PHP, kompilasi Blade, serta unit/feature tests pada pull request.
Lihat hasil aktual pada tab Actions; keberadaan workflow bukan jaminan tes sudah lulus.

Tes mencakup autentikasi, penolakan akses admin, koordinat invalid, radius, absensi
berulang, batas tanggal Jakarta, unique index, isolasi data pengguna, ringkasan,
pagination, dashboard kosong, validasi kantor, dan seeder berulang.
Pengujian paralel terhadap MySQL/InnoDB tetap diperlukan sebelum pemakaian produksi;
tes SQLite tidak membuktikan perilaku row lock MySQL.

## Arsitektur singkat

| Komponen | Tanggung jawab |
| --- | --- |
| AttendanceLocationRequest | Otorisasi karyawan terdaftar dan validasi koordinat |
| AttendanceService | Transaksi, urutan masuk/pulang, waktu server, radius |
| Geofence | Perhitungan jarak Haversine dalam meter |
| AttendanceApiController | Kontrak respons mobile, riwayat dan ringkasan |
| AuthController | Penerbitan serta pencabutan token mobile |
| DashboardController | Agregasi data untuk dashboard admin |

Tanggal dan jam absensi mengikuti server. `user_id`, `date`, dan jam yang dikirim
klien tidak dipakai untuk mencatat absensi. Koordinat masih berasal dari klien;
validasi radius **tidak** menjamin pencegahan GPS palsu.

## Dokumentasi lanjutan

- [Kontrak API dan integrasi Flutter](docs/API.md)
- [Audit, migrasi, dan pengembangan berikutnya](docs/PORTFOLIO.md)

Versi ini masih menggunakan satu lokasi kantor dan satu sesi absensi per hari.
Shift lintas tengah malam, cuti/izin, lembur, payroll, audit trail, serta GPS attestation
belum diimplementasikan. Menit tercatat adalah selisih masuk–pulang dari catatan lengkap,
bukan jam kerja bersih atau dasar penggajian.

Sumber mobile berada di [chips-bodywork-absensi-app](https://github.com/DimastriP2/chips-bodywork-absensi-app).
[Draft PR mobile #1](https://github.com/DimastriP2/chips-bodywork-absensi-app/pull/1)
mengintegrasikan endpoint baru. Jalankan kedua branch bersama untuk pengujian.
ZIP unggahan belum dibandingkan byte-per-byte; pengujian end-to-end di perangkat
belum dilakukan.
