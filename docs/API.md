# Kontrak API mobile

Base URL: `/api`. Kirim `Accept: application/json` dan `Content-Type: application/json`.
Selain login, semua endpoint membutuhkan `Authorization: Bearer <token>`.

## Endpoint

| Method | Path | Input | Hasil |
| --- | --- | --- | --- |
| POST | /login | email, password | message, token, user |
| POST | /logout | — | message; token saat ini dicabut |
| POST | /change-password | old_password, new_password, new_password_confirmation | message |
| GET | /profile | — | message, user, employee |
| POST | /checkin | latitude, longitude | 201; message, attendance |
| POST | /checkout | latitude, longitude | 200; message, attendance |
| GET | /history | month?, page?, per_page? | message, attendances; meta jika pagination |
| GET | /attendance/today | — | date, timezone, server_time, state, attendance |
| GET | /attendance/summary | month? dalam YYYY-MM | month, timezone, summary |
| GET | /office | — | message, office |

Check in/out hanya untuk akun dengan `role=employee` **dan** profil pada tabel
`employees`. Akun hasil registrasi publik tanpa profil tidak dapat absen; admin
membuat akun karyawan melalui menu Data Karyawan.

Login mempertahankan kebijakan awal: login berhasil mencabut token mobile sebelumnya.
Ganti password mempertahankan token perangkat saat ini dan mencabut token mobile lain.
Sesi web bukan bagian dari pencabutan token tersebut.

## Lokasi

```json
{"latitude": -6.2, "longitude": 106.816666}
```

Latitude harus numerik pada rentang -90 sampai 90, longitude -180 sampai 180.
Jarak aktual dibandingkan dengan radius; pembulatan hanya untuk respons dan penyimpanan.
API memakai waktu server `Asia/Jakarta` dan identitas token, bukan jam/ID dari klien.
Client tidak boleh menganggap validasi lokal menggantikan validasi server.

`GET /office` menyediakan nama kantor, latitude/longitude numerik, dan radius dalam meter.
Belum ada verifikasi GPS palsu, bukti foto, atau pengecekan akurasi sensor.

## Status hari ini

```json
{
  "message": "Status absensi hari ini berhasil diambil",
  "date": "2026-09-14",
  "timezone": "Asia/Jakarta",
  "server_time": "2026-09-14T08:00:00+07:00",
  "state": "not_checked_in",
  "attendance": null
}
```

State: `not_checked_in`, `checked_in`, `checked_out`.
Setelah check in/out atau respons `409`, ambil status ini lagi sebelum memperbarui tombol.
Catatan lama yang belum check out tidak dipasangkan otomatis ke hari berikutnya:
shift lintas hari belum didukung.

## Riwayat kompatibel

`GET /history` tetap mengembalikan array pada `attendances` tanpa membungkusnya dalam paginator.
`GET /history?month=2026-09&page=1&per_page=20` menambahkan:

```json
{"meta": {"current_page": 1, "last_page": 2, "per_page": 20, "total": 35}}
```

`per_page` 1–100, default 20 saat pagination digunakan. `page` minimal 1.
`month` harus YYYY-MM yang valid. Halaman melewati akhir menghasilkan array kosong.
Mode tanpa pagination dipertahankan untuk mobile lama dan masih dapat menghasilkan
respons besar; migrasikan Flutter ke pagination sebelum menghapus mode lama.

## Ringkasan bulanan

```json
{
  "message": "Ringkasan absensi berhasil diambil",
  "month": "2026-09",
  "timezone": "Asia/Jakarta",
  "summary": {
    "present_days": 12,
    "completed_days": 11,
    "incomplete_days": 1,
    "recorded_minutes": 5940
  }
}
```

Contoh di atas ilustratif, bukan data produksi. `month` default bulan server saat ini.
Hitungan hanya mencakup pengguna pemilik token dan catatan dengan jam masuk.
`recorded_minutes` menjumlahkan durasi catatan lengkap, dibulatkan ke bawah per catatan
dan mengabaikan durasi negatif. Jam istirahat/lembur belum dihitung.
Tidak ada hitungan alfa atau keterlambatan karena jadwal kerja belum tersedia.

## Status error

| HTTP | Makna / tindakan mobile |
| --- | --- |
| 401 | Login gagal atau token tidak berlaku; arahkan ke login bila sesi kedaluwarsa |
| 403 | Akses tidak diizinkan atau koordinat di luar radius; tampilkan message |
| 404 | Belum check in hari ini atau kantor belum tersedia |
| 409 | Sudah masuk/pulang atau urutan waktu tidak valid; muat ulang status |
| 422 | Data tidak valid; tampilkan errors per field jika tersedia |
| 429 | Terlalu banyak request; patuhi Retry-After |
| 503 | Konfigurasi kantor lama tidak valid; hubungi admin |

Error API tetap JSON meskipun klien tidak mengirim header Accept.
Batas login: 5 request/menit per pasangan email+IP, 30 per IP.
API terautentikasi: 120 request/menit per pengguna; ganti password tambahan 5/menit.
Konfigurasi trusted proxy harus sesuai lingkungan hosting agar IP limiter benar.

## Integrasi Flutter

Implementasi tersedia pada [draft PR mobile #1](https://github.com/DimastriP2/chips-bodywork-absensi-app/pull/1):

- Simpan token pada secure storage, tangani 401 dan logout server.
- Ambil `/office` untuk peta/radius, serta `/attendance/today` saat beranda dibuka.
- Kunci tombol selama request berjalan; setelah timeout, periksa status sebelum mengulang.
- Tampilkan izin GPS ditolak, layanan lokasi mati, timeout, serta respons validasi.
- Gunakan pagination dan filter bulan; jangan hanya menyaring data di perangkat.
- Tampilkan ringkasan dengan label “durasi tercatat”, bukan total gaji/lembur.

Pengujian end-to-end pada perangkat dengan backend berjalan masih diperlukan.
Tes mobile memakai respons API simulasi; kecocokan dengan ZIP unggahan belum diverifikasi.
