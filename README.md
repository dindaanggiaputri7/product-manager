# 🧪 Apotek Arcana: Potion & Relic Inventory Manager

> Sistem manajemen persediaan toko ramuan, gulungan sihir, relik, dan bahan langka. Dibangun dengan **PHP Native (PDO) + MySQL + CSS Flexbox** dengan fokus pada keamanan server-side.

| | |
|---|---|
| **Mini Project** | Product Manager: Apotek Arcana |
| **Mata Kuliah** | Pemrograman Web, Pertemuan 3 (Integrasi PHP, MySQL & UI Styling) |
| **Nama / NIM** | _(isi nama dan NIM)_ |
| **Tema** | Toko apotek fantasi: "produk" = ramuan, gulungan, relik, dan bahan langka; harga dalam Rupiah |
| **Fokus** | CRUD, validasi server-side, PDO prepared statement, anti-XSS, anti-CSRF, pola PRG |
| **Teknologi** | PHP 8.1+, MySQL/MariaDB, HTML5, CSS3 (Box Model + Flexbox), tanpa framework |

---

## 🎯 Ikhtisar

Dengan tema **Apotek Arcana**, aplikasi ini mensimulasikan pengelolaan inventori toko ramuan fantasi dengan kategori Ramuan, Gulungan, Relik, dan Bahan Langka. Aplikasi ini membuktikan konsep yang dipelajari pada Pertemuan 3: alur request–response HTTP, method GET/POST, validasi input, koneksi database dengan PDO, operasi CRUD, keamanan output, dan styling dengan Box Model serta Flexbox.

Setiap operasi mengikuti alur yang sama:

```
Form → normalisasi → validasi → prepared statement → redirect (PRG) → index membaca data → output di-escape
```

## ✅ Kesesuaian dengan Ketentuan Tugas

### Fitur wajib

| Ketentuan | Implementasi | File |
|---|---|---|
| **Create**: nama, kategori, harga, stok | Form "Tambah item" + validasi + `INSERT` prepared statement + PRG | `public/create.php` |
| **Read**: card responsif | `SELECT` + card Flexbox (`flex-wrap`, 1/2/3 kolom sesuai layar) | `public/index.php` |
| **Update**: edit berdasarkan ID | `SELECT by ID` (prepared) + `UPDATE` (prepared) | `public/edit.php` |
| **Delete**: POST + CSRF | Hanya POST, token CSRF, `DELETE` prepared | `public/delete.php` |
| **Validasi**: nama ≥ 3, harga > 0, stok ≥ 0, nama unik | Fungsi `validate_product()` + cek UNIQUE + tangkap error `23000` | `config/helpers.php` |

### Syarat teknis

| Syarat | Bukti |
|---|---|
| `INSERT` / `SELECT by ID` / `UPDATE` / `DELETE` memakai `$pdo->prepare(...)` | `create.php`, `edit.php`, `delete.php` |
| Output memakai `htmlspecialchars($x, ENT_QUOTES, "UTF-8")` | Fungsi `e()` di `config/helpers.php`, dipakai di semua output |
| Tidak ada data ganda saat refresh | Redirect setelah `INSERT` / `UPDATE` / `DELETE` |
| Berkas pengumpulan | Source code, `database/store_db.sql`, dan `README.md` ini |

### Bonus

| Bonus | Status |
|---|---|
| Search/filter dengan GET (aman terhadap SQL injection) | ✅ Ada: kolom cari + chip kategori |
| Pagination | — |
| Upload gambar | — |

### Pemetaan ke kriteria penilaian

| Kriteria | Bobot | Di mana dibuktikan |
|---|---|---|
| CRUD berfungsi | 30% | Skenario uji 1, 8, 9 |
| Keamanan (PDO, escaping, CSRF delete) | 25% | Skenario uji 6, 10, 11, 12 |
| Validasi + PRG | 20% | Skenario uji 2, 3, 4, 5, 7 |
| UI responsif (Box Model + Flexbox) | 15% | Skenario uji 13 + `public/assets/style.css` |
| Struktur proyek + README | 10% | Dokumen ini |

---

## 📁 Struktur Proyek

```
product-manager/
├── config/
│   ├── db.php              # koneksi PDO
│   └── helpers.php         # e(), CSRF, redirect, validasi
├── includes/
│   ├── header.php          # navbar bertema
│   ├── footer.php
│   └── product_form.php    # form dipakai bersama create & edit
├── public/                 # satu-satunya folder yang diakses browser
│   ├── index.php           # READ + search + ringkasan
│   ├── create.php          # CREATE + PRG
│   ├── edit.php            # READ one + UPDATE
│   ├── delete.php          # DELETE + CSRF
│   └── assets/style.css    # Box Model + Flexbox
├── database/
│   └── store_db.sql        # skema + data contoh
├── docs/screenshots/       # tempat screenshot bukti uji
└── README.md
```

Prinsip yang dipakai: koneksi database, halaman fitur, dan CSS **dipisah**, dan setiap halaman punya **satu tanggung jawab**.

---

## 🚀 Cara Menjalankan

**Kebutuhan:** PHP 8.1 atau lebih baru dan MySQL/MariaDB (mis. XAMPP atau Laragon).

1. **Import database**: phpMyAdmin → tab **Import** → pilih `database/store_db.sql`. Atau lewat terminal:
   ```bash
   mysql -u root < database/store_db.sql
   ```
2. **Cek koneksi** di `config/db.php`. Isi `$pass` jika MySQL kamu memakai password.
3. **Jalankan** dari folder project:
   ```bash
   php -S localhost:8000 -t public
   ```
   lalu buka <http://localhost:8000>.

   Dengan XAMPP: letakkan folder di `htdocs`, lalu buka `http://localhost/product-manager/public/`.

---

## 🛡️ Arsitektur Keamanan

| Ancaman | Mekanisme | Implementasi |
|---|---|---|
| SQL Injection | PDO prepared statement | Semua query yang memakai input berparameter; `EMULATE_PREPARES = false` |
| XSS | Escape output | `e()` = `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` |
| CSRF | Token acak per sesi | `bin2hex(random_bytes(32))`, dicek dengan `hash_equals()` di create, edit, delete |
| Aksi destruktif lewat link | Batasi method | `delete.php` menolak selain POST (HTTP 405) |
| Data ganda saat refresh | Post–Redirect–Get | `header('Location: ...'); exit;` |
| Input tidak valid | Validasi server-side | `validate_product()`; validasi browser hanya bantuan UX |
| Nama kembar | UNIQUE + exception | Cek sebelum simpan + `catch PDOException` kode `23000` |
| Bocor info koneksi | Pesan error umum | `config/db.php` tidak menampilkan detail exception |

## 🗄️ Skema Database

```sql
CREATE TABLE products (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)  NOT NULL UNIQUE,
  category   VARCHAR(50)   NOT NULL DEFAULT 'Umum',
  price      DECIMAL(12,2) NOT NULL,
  stock      INT           NOT NULL DEFAULT 0,
  created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);
```

`DECIMAL` dipilih untuk harga karena lebih tepat daripada `float` untuk nilai uang.

---

## 🖥️ Tampilan Antarmuka

Tampilan dibuat **bersih**: latar terang, satu warna utama (ungu arcana), dan warna peringatan hanya dipakai untuk status stok.

- **Navbar** dengan logo botol ramuan dan tombol **+ Tambah item**.
- **Pencarian** nama atau kategori (GET, berparameter).
- **Ringkasan**: jenis item, total unit, nilai gudang (Rupiah), dan jumlah item yang perlu restock.
- **Chip kategori** untuk filter cepat, dengan jumlah item per kategori.
- **Card item (Flexbox)**: ikon botol berwarna sesuai kategori, harga dalam Rupiah, batang level stok, status stok (habis / menipis), tombol Edit dan Hapus.
- **Form** dengan pesan error per field, dan data yang sudah diketik tidak hilang.
- **Responsif**: 3 kolom di layar lebar, 2 kolom di tablet, 1 kolom di HP.

---

## 🧪 Matriks Skenario Uji

Skenario di bawah mengikuti **checklist demo** pada ketentuan tugas dan ditambah pengujian keamanan. Cara pakai:

1. Jalankan aplikasi, lalu lakukan langkah pada tiap skenario.
2. Jika hasilnya sesuai, ubah status `⬜` menjadi `LULUS ✅` (di tabel skenario dan di tabel ringkasan).
3. Ambil screenshot dan simpan di `docs/screenshots/` dengan nama yang tertulis, lalu hilangkan tanda komentar pada baris gambar.

> Status awal **⬜ = belum diuji**. Jangan ubah menjadi LULUS sebelum benar-benar dicoba.

### Skenario 1 — Create item valid

| | |
|---|---|
| **Langkah** | Buka **+ Tambah item**, isi: Nama `Ramuan Kelincahan`, Kategori `Ramuan`, Harga `175000`, Stok `12` → Simpan |
| **Hasil yang diharapkan** | Redirect ke daftar, banner hijau "Item berhasil disimpan.", item muncul paling atas |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/01_create_sukses.png` |

<!-- ![Skenario 1](docs/screenshots/01_create_sukses.png) -->

### Skenario 2 — Nama kurang dari 3 karakter

| | |
|---|---|
| **Langkah** | Tambah item dengan Nama `ZX` |
| **Hasil yang diharapkan** | Ditolak, form tampil lagi dengan pesan "Nama minimal 3 karakter.", data tidak tersimpan |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/02_nama_pendek.png` |

<!-- ![Skenario 2](docs/screenshots/02_nama_pendek.png) -->

### Skenario 3 — Harga nol atau negatif

| | |
|---|---|
| **Langkah** | Tambah item dengan Harga `-1000` (lalu coba juga `0`) |
| **Hasil yang diharapkan** | Ditolak dengan pesan "Harga harus berupa angka > 0.", data tidak tersimpan |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/03_harga_negatif.png` |

<!-- ![Skenario 3](docs/screenshots/03_harga_negatif.png) -->

### Skenario 4 — Stok negatif

| | |
|---|---|
| **Langkah** | Tambah item dengan Stok `-4` |
| **Hasil yang diharapkan** | Ditolak dengan pesan "Stok harus bilangan bulat dan tidak boleh negatif." |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/04_stok_negatif.png` |

<!-- ![Skenario 4](docs/screenshots/04_stok_negatif.png) -->

### Skenario 5 — Nama produk duplikat

| | |
|---|---|
| **Langkah** | Tambah item dengan nama yang sudah ada, mis. `Ramuan Penyembuh` |
| **Hasil yang diharapkan** | Ditolak dengan pesan "Nama produk sudah digunakan.", tanpa error PDO di layar |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/05_duplikat.png` |

<!-- ![Skenario 5](docs/screenshots/05_duplikat.png) -->

### Skenario 6 — Injeksi XSS pada nama

| | |
|---|---|
| **Langkah** | Tambah item dengan Nama `<b>Promo</b>` (coba juga `<script>alert(1)</script>Uji`) |
| **Hasil yang diharapkan** | Tampil sebagai teks apa adanya, tidak menjadi huruf tebal, tidak ada popup |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/06_xss.png` |

<!-- ![Skenario 6](docs/screenshots/06_xss.png) -->

### Skenario 7 — Refresh setelah create (PRG)

| | |
|---|---|
| **Langkah** | Setelah Skenario 1 berhasil, tekan **F5** di halaman daftar |
| **Hasil yang diharapkan** | Tidak ada dialog "Kirim ulang formulir", item tidak bertambah dua kali |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/07_prg.png` |

<!-- ![Skenario 7](docs/screenshots/07_prg.png) -->

### Skenario 8 — Update item

| | |
|---|---|
| **Langkah** | Klik **Edit** pada satu item, ubah harga dan stok → Simpan perubahan |
| **Hasil yang diharapkan** | Form terisi data lama; setelah simpan redirect dengan banner "Item berhasil diperbarui." dan nilai baru tampil |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/08_update.png` |

<!-- ![Skenario 8](docs/screenshots/08_update.png) -->

### Skenario 9 — Delete item

| | |
|---|---|
| **Langkah** | Klik **Hapus** → konfirmasi **OK** |
| **Hasil yang diharapkan** | Muncul konfirmasi; setelah OK item hilang dan banner "Item berhasil dihapus." tampil |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/09_delete.png` |

<!-- ![Skenario 9](docs/screenshots/09_delete.png) -->

### Skenario 10 — Delete lewat GET ditolak

| | |
|---|---|
| **Langkah** | Ketik langsung di address bar: `.../delete.php?id=1` |
| **Hasil yang diharapkan** | Ditolak dengan pesan "Method tidak diizinkan." (HTTP 405), data tidak terhapus |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/10_get_delete.png` |

<!-- ![Skenario 10](docs/screenshots/10_get_delete.png) -->

### Skenario 11 — Token CSRF palsu ditolak

| | |
|---|---|
| **Langkah** | Klik kanan tombol Hapus → **Inspect**, ubah `value` pada `<input name="csrf">` menjadi sembarang teks, lalu klik Hapus |
| **Hasil yang diharapkan** | Ditolak dengan pesan "Token CSRF tidak valid." (HTTP 403), data tidak terhapus |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/11_csrf.png` |

<!-- ![Skenario 11](docs/screenshots/11_csrf.png) -->

### Skenario 12 — Pencarian GET dan SQL injection

| | |
|---|---|
| **Langkah** | Cari `ramuan`, lalu cari `' OR '1'='1` |
| **Hasil yang diharapkan** | Pencarian pertama menampilkan item yang cocok (2 ramuan pada data contoh). Pencarian kedua tidak menampilkan semua data dan tidak error, karena input dianggap teks biasa |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/12_search.png` |

<!-- ![Skenario 12](docs/screenshots/12_search.png) -->

### Skenario 13 — Layar sempit (responsif)

| | |
|---|---|
| **Langkah** | Perkecil jendela browser (atau DevTools → mode perangkat, ±400px) |
| **Hasil yang diharapkan** | Card berpindah dari 3 → 2 → 1 kolom, tidak ada scroll horizontal |
| **Status** | ⬜ |
| **Bukti** | `docs/screenshots/13_responsive.png` |

<!-- ![Skenario 13](docs/screenshots/13_responsive.png) -->

---

## 📊 Ringkasan Hasil Pengujian

| No | Skenario Pengujian | Bukti Singkat | Status |
|---|---|---|---|
| 1 | Create item valid | Banner hijau + item tampil di grid | ⬜ |
| 2 | Nama < 3 karakter | Pesan "Nama minimal 3 karakter." | ⬜ |
| 3 | Harga nol / negatif | Pesan validasi harga | ⬜ |
| 4 | Stok negatif | Pesan validasi stok | ⬜ |
| 5 | Nama duplikat | Pesan "Nama produk sudah digunakan." | ⬜ |
| 6 | Injeksi XSS | Tag tampil sebagai teks | ⬜ |
| 7 | Refresh setelah create (PRG) | Tidak ada data ganda | ⬜ |
| 8 | Update item | Nilai baru tampil + banner | ⬜ |
| 9 | Delete item | Item hilang + banner | ⬜ |
| 10 | Delete lewat GET | HTTP 405 | ⬜ |
| 11 | Token CSRF palsu | HTTP 403 | ⬜ |
| 12 | Pencarian + SQL injection | Filter akurat, input aman | ⬜ |
| 13 | Responsivitas layar | Satu kolom di ±400px | ⬜ |

**Total: ⬜ / 13 skenario lulus** _(perbarui setelah pengujian)_

---

## 💭 Refleksi

**Di bagian mana aplikasi paling rentan: input, query, output, atau alur request?**

> _(Tulis dengan bahasamu sendiri. Contoh arah jawaban: bagian paling rentan adalah **input**, karena semua data dari pengguna tidak bisa dipercaya. Kontrol yang diterapkan: validasi server-side dan normalisasi (input); PDO prepared statement (query); `htmlspecialchars` (output); token CSRF, POST untuk delete, dan PRG (alur request).)_
