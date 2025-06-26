# GAS - Generator CRUD CodeIgniter 4

**GAS** adalah aplikasi generator CRUD berbasis CodeIgniter 4 yang memudahkan pembuatan modul manajemen data secara otomatis, lengkap dengan fitur:
- **CRUD Otomatis** (Controller, Model, View)
- **Soft Delete** & Trash/Restore
- **RBAC (Role-Based Access Control)**
- **Manajemen Menu Dinamis**
- **Pencarian & Pagination AJAX**
- **Responsive Design (Bootstrap 5)**
- **Super Admin Panel**

## Fitur Utama

- **Generator CRUD**  
  Buat modul CRUD baru hanya dengan satu perintah, lengkap dengan controller, model, view, dan stub yang sudah siap pakai.

- **Soft Delete & Trash**  
  Data yang dihapus tidak langsung hilang, melainkan masuk ke halaman Trash. Super Admin dapat melakukan restore atau hapus permanen.

- **RBAC**  
  Hak akses menu dan aksi (view, create, update, delete) dapat diatur per role melalui panel admin.

- **Manajemen Menu**  
  Menu aplikasi dapat diatur secara dinamis, termasuk urutan dan akses per role.

- **Responsive & Modern UI**  
  Tampilan berbasis Bootstrap 5, mendukung desktop dan mobile.

## Cara Install

1. **Clone repository ini**
   ```bash
   git clone https://github.com/username/gas.git
   cd gas
   ```

2. **Install dependency**
   ```bash
   composer install
   ```

3. **Copy file environment**
   ```bash
   cp .env.example .env
   ```

4. **Atur koneksi database di file `.env`**

5. **Jalankan migrasi**
   ```bash
   php spark migrate
   ```

6. **Jalankan server**
   ```bash
   php spark serve
   ```

7. **Akses aplikasi di browser**
   ```
   http://localhost:8080
   ```

## Cara Generate Modul CRUD Baru

```bash
php spark generate:crud <url_path> <menu_name>
```
Contoh:
```bash
php spark generate:crud kendaraan/data-kendaraan "Data Kendaraan"
```

## Hak Akses & Role

- **Super Admin**: Akses penuh, termasuk fitur restore & purge trash.
- **Admin/User**: Hak akses sesuai pengaturan di panel Role Setting.

## Kontribusi

Pull request sangat diterima! Silakan fork repo ini dan ajukan PR untuk fitur/bugfix.

## Lisensi

MIT

---

**Dibuat dengan ❤️ menggunakan CodeIgniter 4**
