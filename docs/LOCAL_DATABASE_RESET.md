# Reset Database Lokal

Dokumentasi cara reset total database lokal untuk development.

## 🚨 PERINGATAN

> **Command ini HANYA bisa dijalankan di environment `local`.**  
> Akan otomatis ABORT jika `APP_ENV` bukan `local`.

---

## Cara Reset via Docker

### Option 1: Reset Total (Semua Tabel)

```bash
# Dari folder infra-shineedu
docker exec shine-api php artisan db:reset-local

# Skip konfirmasi
docker exec shine-api php artisan db:reset-local --force
```

Tabel yang akan di-truncate:

- `sesi_murid_assignment`
- `sesi_murid_materi`
- `sesi_logbook_murid`
- `sesi_absensi_murid`
- `paket_murid_ledger`
- `paket_murid`
- `enrollment`
- `murid`
- `model_has_roles`
- `model_has_permissions`
- `users`

### Option 2: Reset + Fresh Migration + Seed

```bash
docker exec shine-api php artisan migrate:fresh --seed
```

### Option 3: Reset Tabel Murid Saja

```bash
docker exec shine-api php artisan reset:murid-table --force
```

---

## Re-seed Data

Setelah reset, jalankan seeder:

```bash
docker exec shine-api php artisan db:seed
```

---

## Test Bulk Import Setelah Reset

```bash
curl -X POST https://api.shineeducationbali.test/api/v2/murid/bulk \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"nama_lengkap": "Test Tanpa HP", "status": "Aktif"},
      {"nama_lengkap": "Test Dengan HP", "no_hp": "08123456789", "status": "Aktif"}
    ],
    "dry_run": false
  }'
```
