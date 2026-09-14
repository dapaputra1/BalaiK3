# SOP Storage Dokumen Sensitif (Production)

## Tujuan
- Menjaga dokumen layanan tetap private.
- Mencegah dokumen sensitif diakses langsung lewat URL publik.

## Aturan Wajib
- Jangan jalankan `php artisan storage:link` di production untuk dokumen sensitif.
- Semua upload dokumen harus disimpan ke disk private (`storage/app` via disk `local`).
- Akses file hanya melalui endpoint controller yang sudah ada otorisasi.

## Checklist Deploy
1. Pastikan symlink publik tidak ada:
   - Cek: `public/storage` tidak boleh ada.
2. Pastikan route download file berjalan normal:
   - `penawaran.documents.show`
   - `riwayat_pelayanan.billing.show`
   - `riwayat_pelayanan.lhu.show`
   - `petugas.signature`, `petugas.signature.me`
3. Pastikan upload hanya menerima `pdf/doc/docx` dan lolos scanner backend.

## SOP Upload Dokumen
1. Validasi ukuran file dan tipe (`pdf`, `doc`, `docx`).
2. Jalankan scanner sederhana backend (`SafeDocumentUpload`).
3. Simpan file ke disk private (`local`).
4. Simpan path file ke database.
5. Tampilkan file hanya via route aman (bukan `asset('storage/...')`).

## SOP Insiden
1. Jika ditemukan dokumen publik:
   - Putus akses publik (hapus symlink/route publik).
   - Pindahkan file ke private.
   - Audit log akses dan akun terkait.
2. Jika ada file terindikasi malware:
   - Blok upload.
   - Simpan metadata kejadian (user, waktu, nama file).
   - Lakukan review manual oleh admin.

## Backup
- Backup harian:
  - Database.
  - Folder `storage/app`.
- Uji restore secara berkala di environment non-production.

