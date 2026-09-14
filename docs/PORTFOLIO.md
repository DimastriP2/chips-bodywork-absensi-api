# Audit dan arah portofolio

## Temuan yang ditangani

| Kondisi awal | Perubahan |
| --- | --- |
| Koordinat hanya diwajibkan terisi | Validasi numerik dan rentang, termasuk pengaturan kantor |
| Check lalu insert tanpa transaksi | Lock baris user dalam transaksi dan unique user_id+date |
| Check out dapat beradu saat request bersamaan | Serialisasi per pengguna; jam pulang pertama dipertahankan |
| Akun employee tanpa profil bisa mengirim absensi | Check in/out memerlukan profil karyawan yang disediakan admin |
| Auth::attempt dipakai untuk login token | Verifikasi password tanpa membuat sesi web |
| Tidak ada logout API atau rate limit login | Pencabutan token saat ini dan limiter |
| Semua riwayat selalu diambil | Filter bulan serta pagination opt-in kompatibel |
| Dashboard tiga angka dasar | Metrik masuk/pulang, catatan terkini, tren, empty state, layout responsif |
| README template Laravel | Setup proyek, demo, kontrak API, batasan, pengujian |

## Migrasi database yang sudah berisi data

Sebelum upgrade, buat backup dan jalankan migrasi pada salinan database.
Pastikan proses yang menulis absensi berhenti selama penambahan unique index.

```sql
SELECT user_id, date, COUNT(*) AS total
FROM attendances
GROUP BY user_id, date
HAVING COUNT(*) > 1;
```

Migration baru menolak berjalan jika menemukan pasangan ganda. Ia tidak menghapus
atau memilih catatan secara otomatis. Tinjau sumber catatan, simpan arsip, lalu koreksi
sesuai data yang benar sebelum menjalankan `php artisan migrate` lagi.
Unique index tetap menolak duplikasi meskipun writer lain tidak memakai service.

Waktu aplikasi sudah Asia/Jakarta sebelum perubahan ini dan tetap sama.
Tidak ada konversi ulang data lama. Periksa data impor yang mungkin menggunakan timezone lain.
Office lama dengan koordinat/radius tidak valid perlu diperbaiki melalui dashboard.

Lock per user dirancang untuk database dengan row locking, misalnya MySQL/InnoDB.
SQLite cocok untuk demo dan tes fungsional; uji beban request paralel di database target
sebelum menyebut perilaku concurrency sudah terverifikasi di produksi.

## Batasan yang masih terbuka

- ZIP Flutter belum dapat diekstrak pada sesi ini. Persentase kesamaan dengan repo
  belum diketahui; repo berisi Laravel, bukan sumber mobile.
- Belum ada pengujian perangkat Android/iOS atau screenshot hasil render dashboard.
- Satu kantor, satu absensi per tanggal, tanpa shift malam dan mekanisme koreksi absensi.
- Koordinat dari perangkat dapat dipalsukan; Haversine hanya menghitung jarak.
- Registrasi web bawaan masih tersedia, namun akun tanpa profil tidak dapat absen.
  Untuk deployment perusahaan, tentukan kebijakan undangan/provisioning akun.
- Penghapusan karyawan yang sudah ada masih menghapus user beserta absensi melalui cascade.
  Sebelum memakai data nyata, rancang penonaktifan akun dan retensi riwayat.
- CRUD user+employee lama belum memakai transaksi menyeluruh. Audit perubahan profil,
  email, dan penghapusan akun adalah pekerjaan lanjutan.
- Ekspor CSV/PDF lama masih mengambil semua baris dan perlu audit volume, filter,
  serta formula injection CSV sebelum digunakan dengan data bebas dari pengguna.
- Tidak ada audit trail, approval izin/cuti, kalender kerja, atau otorisasi multi-cabang.
- Ringkasan bukan kalkulasi payroll. Tidak menyimpulkan alfa, terlambat, lembur, atau lokasi real-time.

## Urutan pengembangan lanjutan

1. **Integrasi mobile:** secure storage, state status server, pagination, error dan retry UX.
2. **Siklus hidup karyawan:** nonaktifkan akun, cabut akses, pertahankan arsip, transaksi CRUD.
3. **Jadwal kerja:** shift, toleransi keterlambatan, hari libur, lintas tengah malam.
4. **Izin/cuti:** pengajuan, approval admin, bukti pendukung, histori keputusan.
5. **Audit dan laporan:** jejak perubahan, ekspor aman, filter konsisten, uji volume.
6. **Portofolio:** video demo 2–3 menit, screenshot nyata, diagram arsitektur, hasil tes,
   dan studi kasus keputusan teknis beserta keterbatasannya.

## Contoh deskripsi portofolio

“Mengembangkan backend absensi lokasi menggunakan Laravel dan Sanctum dengan dashboard
admin, validasi geofence di server, transaksi absensi per karyawan, unique constraint,
ringkasan kehadiran, serta feature tests untuk autentikasi dan integritas data.”

Tambahkan klaim Flutter, deployment, pengguna aktif, atau hasil performa hanya setelah
implementasi dan buktinya tersedia.

## Referensi teknis

- [Laravel 12 query builder — pessimistic locking](https://laravel.com/docs/12.x/queries#pessimistic-locking)
- [Laravel 12 Sanctum — revoking tokens](https://laravel.com/docs/12.x/sanctum#revoking-tokens)
