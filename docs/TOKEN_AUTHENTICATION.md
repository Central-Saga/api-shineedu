# Token Authentication & Idle Timeout

## Overview

Sistem autentikasi menggunakan Laravel Sanctum dengan dua mekanisme timeout:

1. **Token Expiration**: 24 jam dari waktu token dibuat
2. **Idle Timeout**: 15 menit tanpa aktivitas

## Cara Kerja

### Token Expiration (24 jam)

- Token akan expired **24 jam** setelah dibuat (dihitung dari waktu login)
- Diatur di `AuthController::login()` dengan `now()->addDay()`
- User harus login ulang setelah 24 jam, meskipun masih aktif

### Idle Timeout (15 menit)

- Jika user **tidak melakukan request apapun** selama **15 menit**, token akan otomatis di-revoke
- Setiap kali ada request, `last_used_at` akan di-update
- Middleware `CheckTokenActivity` akan:
    - Memeriksa `last_used_at` pada setiap request
    - Jika > 15 menit, revoke token dan return 401
    - Jika ≤ 15 menit, update `last_used_at` dan lanjutkan request

## Implementasi

### 1. Middleware: CheckTokenActivity

File: `app/Http/Middleware/CheckTokenActivity.php`

Middleware ini:

- Memeriksa apakah token sudah idle > 15 menit
- Revoke token jika idle terlalu lama
- Update `last_used_at` untuk setiap request yang valid

### 2. Registrasi Middleware

File: `bootstrap/app.php`

```php
'check.token.activity' => \App\Http\Middleware\CheckTokenActivity::class,
```

### 3. Penerapan di Routes

File: `routes/api.php`

```php
Route::middleware(['auth:sanctum', 'check.token.activity'])->group(function () {
    // All protected routes
});
```

## Response Codes

### 401 Unauthorized - Token Expired (24 jam)

```json
{
    "success": false,
    "message": "Unauthenticated.",
    "data": null
}
```

### 401 Unauthorized - Idle Timeout (15 menit)

```json
{
    "success": false,
    "message": "Sesi Anda telah berakhir karena tidak ada aktivitas selama 15 menit. Silakan login kembali.",
    "data": null
}
```

## Konfigurasi

### Mengubah Idle Timeout

Edit file: `app/Http/Middleware/CheckTokenActivity.php`

```php
// Define idle timeout in minutes
$idleTimeoutMinutes = 15; // Ubah nilai ini
```

### Mengubah Token Expiration

Edit file: `app/Modules/Identity/Http/Controllers/Api/V2/AuthController.php`

```php
// Create token with 1 day expiration
$token = $user->createToken('auth-token', ['*'], now()->addDay()); // Ubah addDay() sesuai kebutuhan
```

Atau ubah di `config/sanctum.php`:

```php
'expiration' => env('SANCTUM_EXPIRATION', 1440), // dalam menit (1440 = 24 jam)
```

## Testing

### Test Idle Timeout

1. Login dan dapatkan token
2. Tunggu 15 menit tanpa melakukan request apapun
3. Lakukan request dengan token tersebut
4. Harusnya mendapat response 401 dengan message idle timeout

### Test Active Session

1. Login dan dapatkan token
2. Lakukan request setiap < 15 menit
3. Token tetap valid selama belum mencapai 24 jam

### Test Token Expiration

1. Login dan dapatkan token
2. Tunggu 24 jam (atau ubah expiration menjadi lebih pendek untuk testing)
3. Lakukan request dengan token tersebut
4. Harusnya mendapat response 401 unauthenticated

## Frontend Integration

Frontend harus menangani kedua jenis 401:

1. Redirect ke login page
2. Tampilkan pesan yang sesuai kepada user
3. Clear token dari storage

```typescript
// Example handling
if (response.status === 401) {
    // Clear token
    localStorage.removeItem("token");

    // Show message
    if (response.data.message.includes("tidak ada aktivitas")) {
        toast.error("Sesi Anda telah berakhir karena tidak ada aktivitas");
    } else {
        toast.error("Sesi Anda telah berakhir, silakan login kembali");
    }

    // Redirect to login
    router.push("/login");
}
```
