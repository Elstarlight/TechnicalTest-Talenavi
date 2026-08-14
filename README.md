# Technical Test - Backend Developer (Talenavi)

Aplikasi Todo List sederhana pakai Laravel. Ada 2 fitur utama: bikin todo lewat API, dan export data todo ke Excel dengan berbagai filter.

Repo: https://github.com/Elstarlight/TechnicalTest-Talenavi

## Stack yang dipakai

- PHP 8.4
- Laravel 13
- SQLite
- maatwebsite/excel (v3.1) buat generate file Excel

## Struktur data Todo

- title (string, wajib)
- assignee (string, opsional)
- due_date (date, wajib, tidak boleh tanggal yang sudah lewat)
- time_tracked (angka, default 0)
- status (pending / open / in_progress / completed, default pending)
- priority (low / medium / high)

## Cara install & jalanin

1. Clone repo

```
git clone https://github.com/Elstarlight/TechnicalTest-Talenavi.git
cd TechnicalTest-Talenavi
```

2. Install dependency

```
composer install
```

Catatan: package maatwebsite/excel butuh extension zip aktif di PHP. Kalau pas install muncul error soal ext-zip, buka php.ini, cari baris `;extension=zip`, hapus tanda titik koma di depannya jadi `extension=zip`, terus restart server/Laragon.

Kalau install package lewat PowerShell, tulis versinya pakai tanda kutip supaya tanda `^` tidak kepotong, contoh:

```
composer require "maatwebsite/excel:^3.1"
```

3. Setup env

```
cp .env.example .env
php artisan key:generate
```

Di file .env pastikan koneksi database-nya sqlite:

```
DB_CONNECTION=sqlite
DB_DATABASE=/path/lengkap/ke/database/database.sqlite
```

Kalau file database.sqlite belum ada, bikin dulu:

```
touch database/database.sqlite
```

(kalau di Windows PowerShell: `New-Item database\database.sqlite -ItemType File`)

4. Migrate & seed

```
php artisan migrate
php artisan db:seed --class=TodoSeeder
```

5. Jalankan server

```
php artisan serve
```

Nanti bisa diakses di http://127.0.0.1:8000

## Endpoint API

- POST /api/todos — bikin todo baru
- GET /api/todos — ambil semua todo
- GET /api/todos/{id} — detail satu todo
- PUT /api/todos/{id} — update todo
- DELETE /api/todos/{id} — hapus todo
- GET /api/todos/export — export ke Excel

Contoh body buat create todo:

```
{
    "title": "Review project proposal",
    "assignee": "John",
    "due_date": "2026-09-01",
    "time_tracked": 0,
    "status": "pending",
    "priority": "high"
}
```

## Filter di export Excel

Endpoint export (`/api/todos/export`) bisa difilter pakai query param berikut, dan bisa dikombinasikan:

- title -> partial match, contoh: ?title=Review
- assignee -> bisa banyak, dipisah koma, contoh: ?assignee=John,Doe
- due_date -> pakai range, contoh: ?start=2026-01-01&end=2026-12-31
- time_tracked -> pakai range, contoh: ?min=10&max=50
- status -> bisa banyak, dipisah koma, contoh: ?status=pending,in_progress
- priority -> bisa banyak, dipisah koma, contoh: ?priority=low,high

File Excel hasil export ada 6 kolom (title, assignee, due_date, time_tracked, status, priority), plus baris ringkasan di bawah yang isinya total todo dan total time_tracked.

## Postman Collection

Ada di folder /postman, nama filenya TalenaviTodoApp.postman_collection.json. Isinya dibagi 2 folder:

- Todo CRUD: create (sukses & yang divalidasi gagal), get all, get detail, update, delete
- Excel Export: export tanpa filter, dan beberapa skenario export dengan filter berbeda-beda

Cara pakainya tinggal import file itu ke Postman, terus pastikan server lokal udah nyala sebelum jalanin request-nya.

## Validasi

- due_date tidak bisa diisi tanggal yang sudah lewat, kalau dilanggar responsenya 422
- status otomatis keisi "pending" kalau tidak dikirim waktu create
