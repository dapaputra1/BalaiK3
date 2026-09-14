# Daftar Halaman Petugas dan Role Akses

Dokumen ini merangkum semua halaman petugas yang saat ini terbuka berdasarkan middleware role di [routes/web.php](/d:/Program/VPS-Web-K3/routes/web.php:1).

Catatan:
1. Dokumen ini fokus pada halaman petugas, yaitu route `GET` yang menampilkan halaman, preview, dokumen, atau file pendukung untuk role petugas.
2. Aksi seperti `store`, `update`, `submit`, `approve`, `reply`, dan `delete` tidak dirinci satu per satu karena bukan halaman.
3. Beberapa halaman masih bisa memiliki validasi tambahan di controller, sehingga middleware route bukan satu-satunya lapisan otorisasi.

## 1. Daftar Role Petugas Aktif

1. Super Administrator (`superadmin`)
2. Administrator (`admin`)
3. Manajemen Administrasi (`ma`)
4. Penyelia (`penyelia`)
5. Analis (`analis`)
6. Manajer Puncak (`mp`)
7. Manajer Teknis (`mt`)
8. Pengendali Mutu (`qc`)
9. PCU (`pcu`)

## 2. Halaman Umum Petugas

1. Dashboard:
   Super Administrator, Administrator, Manajemen Administrasi, Penyelia, Analis, Manajer Puncak, Manajer Teknis, Pengendali Mutu, dan PCU.
2. Permohonan:
   Super Administrator, Administrator, Manajemen Administrasi, Penyelia, Analis, Manajer Puncak, Manajer Teknis, Pengendali Mutu, dan PCU.
3. Ekspor Permohonan:
   Super Administrator, Administrator, Manajemen Administrasi, Penyelia, Analis, Manajer Puncak, Manajer Teknis, Pengendali Mutu, dan PCU.
4. Profil Petugas:
   Super Administrator, Administrator, Manajemen Administrasi, Penyelia, Analis, Manajer Puncak, Manajer Teknis, Pengendali Mutu, dan PCU.
5. Tanda Tangan Petugas Sendiri:
   Semua petugas yang sudah login.
6. Tanda Tangan Petugas Lain:
   Semua user yang sudah login, mengikuti validasi controller.
7. Tanda Tangan Permohonan:
   Semua user yang sudah login, mengikuti validasi controller.
8. Dokumen Lampiran Penawaran:
   Semua user yang sudah login, mengikuti validasi controller.
9. Notifikasi:
   Semua user yang sudah login.

## 3. Halaman Manajemen

1. Kelola Petugas:
   Super Administrator.
2. Kelola Pengguna:
   Super Administrator.
3. Kelola Berita:
   Super Administrator, Administrator.
4. Kelola Jejaring:
   Super Administrator, Administrator.
5. Kelola Media Sosial:
   Super Administrator, Administrator.
6. Kelola Parameter Layanan:
   Super Administrator.
7. Kelola Paket Layanan:
   Super Administrator.
8. Kelola Kategori Layanan:
   Super Administrator.
9. Kelola Parameter Batas Deteksi:
   Super Administrator.
10. Kelola Rumus Absorbansi:
    Super Administrator.
11. Referensi Rumus Absorbansi:
    Super Administrator.
12. Feedback:
    Super Administrator, Administrator.
13. Ulasan Permohonan:
    Super Administrator, Administrator.

## 4. Halaman Alur Kerja Utama

1. Disposisi:
   Super Administrator, Manajer Puncak, Manajer Teknis.
2. Kaji Ulang:
   Super Administrator, Manajer Teknis.
3. Penawaran:
   Super Administrator, Administrator.
4. Penjadwalan:
   Super Administrator, Penyelia.
5. Persetujuan Manajemen Administrasi:
   Super Administrator, Manajemen Administrasi.
6. Dokumen SPT:
   Super Administrator, Administrator.
7. Pengujian:
   Super Administrator, PCU.
8. Verifikasi PCU:
   Super Administrator, PCU.
9. BAP:
   Super Administrator, Administrator, PCU.
10. Verifikasi Pengujian:
    Super Administrator, Penyelia.
11. Koding:
    Super Administrator, Administrator.
12. Preparasi Analisa:
    Super Administrator, Analis.
13. Verifikasi Hasil Analisa:
    Super Administrator, Pengendali Mutu.
14. Draft LHU:
    Super Administrator, Administrator, PCU.
15. QC LHU:
    Super Administrator, Pengendali Mutu.
16. Penandatanganan LHU:
    Super Administrator, Manajer Teknis.
17. Surat Tagihan:
    Super Administrator, Administrator.
18. Invoice:
    Super Administrator, Administrator.
19. Kode Billing:
    Super Administrator, Administrator.
20. Penerbitan Surat Keterangan:
    Super Administrator, Administrator.
21. Penyerahan LHU:
    Super Administrator, Administrator.

## 5. Halaman Detail, Preview, dan Dokumen Pendukung

1. Dokumen Customer pada Disposisi:
   Super Administrator, Manajer Puncak, Manajer Teknis.
2. Dokumen SPT Preview:
   Super Administrator, Administrator.
3. Dokumen SPT Bertanda Tangan:
   Super Administrator, Administrator, PCU.
4. File Pengujian:
   Super Administrator, Administrator, Penyelia, PCU.
5. Riwayat Preparasi Analisa:
   Super Administrator, Analis.
6. Ekspor Riwayat Preparasi Analisa:
   Super Administrator, Analis.
7. Preview Hasil pada Riwayat Preparasi Analisa:
   Super Administrator, Analis.
8. Data Hasil pada Riwayat Preparasi Analisa:
   Super Administrator, Analis.
9. Referensi Formula Preparasi Analisa:
   Super Administrator, Analis.
10. Preview Penyerahan pada Preparasi Analisa:
    Super Administrator, Analis.
11. Preview Hasil Verifikasi:
    Super Administrator, Pengendali Mutu.
12. Dokumen Word Draft LHU:
    Super Administrator, Administrator, PCU.
13. Dokumen Word Semua Draft LHU:
    Super Administrator, Administrator, PCU.
14. File Final Draft LHU:
    Super Administrator, Administrator, PCU.
15. File Revisi QC Draft LHU:
    Super Administrator, Administrator, PCU.
16. File Final QC LHU:
    Super Administrator, Pengendali Mutu.
17. File Revisi QC LHU:
    Super Administrator, Pengendali Mutu.
18. File Final Penandatanganan LHU:
    Super Administrator, Manajer Teknis.
19. File LHU Bertanda Tangan:
    Super Administrator, Manajer Teknis.
20. Surat Tagihan Preview:
    Super Administrator, Administrator.
21. Invoice Preview:
    Super Administrator, Administrator.
22. Billing Preview:
    Super Administrator, Administrator.
23. Surat Keterangan Preview:
    Super Administrator, Administrator.
24. File Penyerahan LHU Bertanda Tangan:
    Super Administrator, Administrator.

## 6. Ringkasan Halaman per Role

1. Super Administrator (`superadmin`)
   Semua halaman petugas.

2. Administrator (`admin`)
   Dashboard, permohonan, ekspor permohonan, profil petugas, kelola berita, kelola jejaring, kelola media sosial, feedback, ulasan permohonan, penawaran, dokumen SPT, BAP, koding, draft LHU, surat tagihan, invoice, billing, penerbitan surat keterangan, penyerahan LHU, dokumen SPT preview, dokumen SPT bertanda tangan, file pengujian, dokumen Word draft LHU, file final draft LHU, file revisi QC draft LHU, surat tagihan preview, invoice preview, billing preview, surat keterangan preview, dan file penyerahan LHU bertanda tangan.

3. Manajemen Administrasi (`ma`)
   Dashboard, permohonan, ekspor permohonan, profil petugas, dan persetujuan manajemen administrasi.

4. Penyelia (`penyelia`)
   Dashboard, permohonan, ekspor permohonan, profil petugas, penjadwalan, verifikasi pengujian, dan file pengujian.

5. Analis (`analis`)
   Dashboard, permohonan, ekspor permohonan, profil petugas, preparasi analisa, riwayat preparasi analisa, ekspor riwayat preparasi analisa, preview hasil riwayat, data hasil riwayat, referensi formula preparasi analisa, dan preview penyerahan preparasi analisa.

6. Manajer Puncak (`mp`)
   Dashboard, permohonan, ekspor permohonan, profil petugas, disposisi, dan dokumen customer pada disposisi.

7. Manajer Teknis (`mt`)
   Dashboard, permohonan, ekspor permohonan, profil petugas, disposisi, dokumen customer pada disposisi, kaji ulang, penandatanganan LHU, file final penandatanganan LHU, dan file LHU bertanda tangan.

8. Pengendali Mutu (`qc`)
   Dashboard, permohonan, ekspor permohonan, profil petugas, verifikasi hasil analisa, preview hasil verifikasi, QC LHU, file final QC LHU, dan file revisi QC LHU.

9. PCU (`pcu`)
   Dashboard, permohonan, ekspor permohonan, profil petugas, pengujian, verifikasi PCU, BAP, draft LHU, dokumen SPT bertanda tangan, file pengujian, dokumen Word draft LHU, file final draft LHU, dan file revisi QC draft LHU.

## 7. Catatan Penting

1. Daftar di atas mengikuti route aktif pada tanggal dokumen ini diperbarui.
2. Jika middleware role di route berubah, dokumen ini harus ikut diperbarui.
3. Sumber utama dokumen ini adalah [routes/web.php](/d:/Program/VPS-Web-K3/routes/web.php:97).
4. Beberapa halaman file atau preview tetap dapat memiliki pembatasan tambahan di level controller atau model binding.
