# Panduan Deploy Smart TU ke cPanel (PostgreSQL)

Panduan langkah demi langkah men-deploy aplikasi Smart TU (Laravel + PWA) dari Git ke
hosting cPanel yang menyediakan **PostgreSQL**. Basis lokal = `smart_tu`, skema `public`.

> Prasyarat penting: cPanel Anda **harus** mendukung **PostgreSQL** (bukan hanya
> MySQL/MariaDB) karena `.env` memakai `DB_CONNECTION=pgsql`. Pastikan PHP versi 8.1+
> dengan ekstensi `pgsql` dan `pdo_pgsql` aktif.

---

## 1. Persiapkan kode di Git

Aplikasi sudah menjadi repo Git dan terhubung ke:

```
https://github.com/hamdanmahalli/Muadalah.git    (branch: main)
```

File `.env` dan `/vendor` **tidak ikut** ter-commit (sudah di `.gitignore`), jadi tidak
akan terkirim ke server. Pastikan semua perubahan terakhir sudah di-push:

```bash
git add -A
git commit -m "persiapan deploy"
git push origin main
```

---

## 2. Deploy ke cPanel (pilih salah satu)

### Opsi A — Git Version Control (via dashboard cPanel)
1. Buka cPanel → **Git Version Control** → **Create**.
2. Repository Path: folder untuk aplikasi, mis. `public_html/smarttu` (atau arahkan
   `document root` jika dipakai sebagai domain utama).
3. Repository URL: `https://github.com/hamdanmahalli/Muadalah.git`
   (guna `https://github.com/.../Muadalah.git`).
4. Branch: `main`.
5. Klik **Create**, lalu **Manage** → **Update from Remote** untuk menarik kode.

### Opsi B — SSH (`git pull`)
Jika cPanel Anda menyediakan Terminal/SSH:

```bash
cd ~/public_html/smarttu
git pull origin main
```

> Catatan struktur: dokumen root aplikasi adalah folder `public/`. Pada cPanel, konten di
> `public_html/` langsung dianggap web root, sehingga Anda **tidak** bisa langsung menaruh
> seluruh isi project di `public_html/` (karena `app/`, `bootstrap/`, `vendor/` akan ikut
> terbuka di web). **Disarankan** memakai **subdomain** (mis. `app.domain.com`) yang
> document root-nya diarahkan ke `public_html/smarttu/public`. Alternatifnya, deploy
> seluruh project di luar web root lalu symlink `public/`. Ini hal terpenting agar file
> sensitif (`database/`, `.env`) tidak bisa diakses publik.

---

## 3. Buat Database PostgreSQL di cPanel

1. cPanel → **PostgreSQL Databases**.
2. Buat database, mis. `app_smarttu`.
3. Buat user + password, lalu **add user to database** dengan **ALL PRIVILEGES**.
4. Catat: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_HOST` (biasanya
   `localhost`) dan `DB_PORT` (default `5432`).

---

## 4. Buat file `.env` di server

Di folder project cPanel (`public_html/smarttu`), buat file baru `.env` (contoh isi):

```env
APP_NAME="Smart TU"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://app.domain.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=app_smarttu
DB_USERNAME=app_smarttu_user
DB_PASSWORD=GANTI_DENGAN_PASSWORD_ANDA

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file

# Web Push Notifikasi — generate key di https://web-push-codelab.glitch.me
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:admin@pendaftar.com

# Notifikasi terjadwal: false (matikan) atau true (aktifkan)
NOTIFIKASI_AKTIF=false

MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@pendaftar.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Sesuaikan `APP_URL`, kredensial DB, dan nilai VAPID sesuai akun Anda. Teks di atas juga
tersedia sebagai `cpel .env.example` kemudian diisi ulang.

> **Tanpa `APP_KEY`** aplikasi tidak akan jalan. Langkah 5 akan mengisinya.

---

## 5. Jalankan instalasi & migrasi

Dari Terminal/SSH di folder `public_html/smarttu`:

```bash
# 1) Pasang dependency PHP (dengan PHP CLI aktif)
composer install --no-dev --optimize-autoloader

# 2) Generate kunci aplikasi
php artisan key:generate

# 3) Kosongkan & bangun ulang tabel + isi data master
php artisan migrate:fresh --seed --force

# 4) Seeder ini TIDAK otomatis dipanggil DatabaseSeeder, jalankan manual:
php artisan db:seed --class=PermissionSeeder --force
php artisan db:seed --class=GenerateUserGuruSeeder --force

# 5) Reset cache permission
php artisan permission:cache-reset

# 6) Setel akun admin ke role Administrator (agar menu @can tampil)
php artisan tinker --execute='$u = \App\Models\User::find(1); $u->assignRole("Administrator");'
```

### Isi data referensi yang tidak ada di seeder

Setelah migrasi, tabel `kelas`, `periodes`, dan `hari_operasional` **kosong** dan wajib
diisi agar aplikasi berfungsi. Jalankan via tinker:

```bash
php artisan tinker --execute='DB::table("kelas")->insert([["nama_kelas"=>"7-A"],["nama_kelas"=>"7-B"],["nama_kelas"=>"8-A"],["nama_kelas"=>"8-B"],["nama_kelas"=>"9-A"],["nama_kelas"=>"9-B"]]);'

php artisan tinker --execute='DB::table("periodes")->insert([["tahun_ajaran"=>"2026/2027","semester"=>"Ganjil","is_active"=>true,"tanggal_mulai"=>"2026-07-15","tanggal_selesai"=>"2026-12-07"],["tahun_ajaran"=>"2026/2027","semester"=>"Genap","is_active"=>false,"tanggal_mulai"=>null,"tanggal_selesai"=>null]]);'

php artisan tinker --execute='DB::table("hari_operasional")->insert([["hari"=>"Sabtu","is_active"=>true,"max_jam"=>10,"keterangan"=>"Hari Normal"],["hari"=>"Ahad","is_active"=>true,"max_jam"=>8,"keterangan"=>"Hari Normal"],["hari"=>"Senin","is_active"=>true,"max_jam"=>10,"keterangan"=>"Hari Normal"],["hari"=>"Selasa","is_active"=>true,"max_jam"=>10,"keterangan"=>"Hari Normal"],["hari"=>"Rabu","is_active"=>true,"max_jam"=>10,"keterangan"=>"Hari Normal"],["hari"=>"Kamis","is_active"=>true,"max_jam"=>10,"keterangan"=>"Hari Normal"],["hari"=>"Jumat","is_active"=>false,"max_jam"=>0,"keterangan"=>"Hari Pendek (Persiapan Jumat)"]]);'
```

> Jika Terminal tidak tersedia di cPanel, tempel ketiga blok di atas ke tool
> **Remote MySQL / phpMyAdmin** untuk PostgreSQL milik cPanel, atau lewat **php artisan
> tinker** pada SSH.

---

## 6. Optimasi & izin folder

```bash
# Buat symlink storage agar upload tampil
php artisan storage:link

# Aktifkan cache agar cepat di production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Pastikan direktori storage dapat ditulis
chmod -R 775 storage bootstrap/cache
```

> `php artisan config:cache` akan menyimpan isi `.env` ke cache. Jika Anda mengubah
> `.env` setelah ini, jalankan `php artisan config:clear` lalu `config:cache` lagi.

---

## 7. Verifikasi instalasi

- Buka `https://app.domain.com` → harus muncul halaman login.
- Login **admin** `admin` / `password123`.
- Login **guru** `NIG` (contoh `1001`) / `123456`.
- Cek dashboard, sidebar (menu berbasis `@can`), dan log in tiap role.
- Buka DevTools → Console, pastikan service worker ter-registrasi tanpa error.

---

## 8. Catatan penting khusus PWA & cPanel

Aplikasi ini adalah **PWA** dan memakai **path absolut** di frontend:

- Service worker didaftarkan di `/sw.js` (layout `app.blade.php`).
- Manifest `manifest.json` + ikon `/icons/*` dan `start_url: "/dashboard-guru"`.

Karena path-nya **root-relative**, aplikasi **harus** berada di **root/subdomain**, bukan
di subfolder seperti `https://domain.com/smarttu`. Jika memakai subfolder, `/sw.js` akan
mengarah ke `https://domain.com/sw.js` (salah). Gunakan **subdomain** (mis. `app.domain.com`)
yang document root-nya menunjuk langsung ke `public/`, atau sesuaikan path PWA.

Frontend memakai Tailwind CSS yang di-compile via **Vite**. Pastikan `public/build/`
ter-upload ke server (hasil dari `npm run build`). Folder `public/build/` sudah di-include
di `.gitignore`, jadi perlu di-build di server atau upload manual setelah `git pull`.

---

## Ringkasan perintah inti (SSH)

```bash
cd ~/public_html/smarttu
git pull origin main
npm install && npm run build
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate:fresh --seed --force
php artisan db:seed --class=PermissionSeeder --force
php artisan db:seed --class=GenerateUserGuruSeeder --force
php artisan permission:cache-reset
# ... (isi kelas / periodes / hari_operasional seperti langkah 5)
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
