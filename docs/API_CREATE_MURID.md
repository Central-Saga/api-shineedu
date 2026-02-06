# API Documentation: Create Murid (Student)

## Endpoint

```
POST /api/v2/murid
```

## Headers

```json
{
    "Authorization": "Bearer {token}",
    "Content-Type": "application/json",
    "Accept": "application/json"
}
```

## Request Body

### Required Fields

Only **1 field** is required:

| Field          | Type   | Required   | Description                              |
| -------------- | ------ | ---------- | ---------------------------------------- |
| `nama_lengkap` | string | ✅ **YES** | Full name of the student (max 255 chars) |

### Optional Fields (Nullable)

#### Personal Information

| Field           | Type   | Nullable | Validation               | Description                       |
| --------------- | ------ | -------- | ------------------------ | --------------------------------- |
| `jenis_kelamin` | string | ✅       | `L` or `P`               | Gender (L=Laki-laki, P=Perempuan) |
| `tanggal_lahir` | string | ✅       | Date format (YYYY-MM-DD) | Date of birth                     |
| `no_hp`         | string | ✅       | Min 8, Max 20 chars      | Student's phone number            |
| `email`         | string | ✅       | Valid email, Max 255     | Student's email                   |
| `alamat`        | string | ✅       | -                        | Address                           |

#### Academic Information

| Field           | Type    | Nullable | Validation                    | Description        |
| --------------- | ------- | -------- | ----------------------------- | ------------------ |
| `jenjang_id`    | integer | ✅       | Must exist in `jenjang` table | Education level ID |
| `sekolah_asal`  | string  | ✅       | Max 255 chars                 | Previous school    |
| `kelas_sekolah` | string  | ✅       | Max 50 chars                  | Grade/class        |

#### Guardian Information

| Field           | Type   | Nullable | Validation           | Description                               |
| --------------- | ------ | -------- | -------------------- | ----------------------------------------- |
| `nama_wali`     | string | ✅       | Max 255 chars        | Guardian's name                           |
| `no_hp_wali`    | string | ✅       | Max 20 chars         | Guardian's phone number                   |
| `email_wali`    | string | ✅       | Valid email, Max 255 | Guardian's email                          |
| `hubungan_wali` | string | ✅       | Max 50 chars         | Relationship (e.g., "Orang Tua", "Kakak") |

#### Additional Information

| Field              | Type   | Nullable | Validation             | Description                                      |
| ------------------ | ------ | -------- | ---------------------- | ------------------------------------------------ |
| `catatan_khusus`   | string | ✅       | -                      | Special notes                                    |
| `kebutuhan_khusus` | string | ✅       | -                      | Special needs                                    |
| `status`           | string | ✅       | `Aktif` or `Non Aktif` | Student status (default: `Aktif`)                |
| `password`         | string | ✅       | Min 8 chars            | Password for student account (if email provided) |

### Auto-Generated Fields

| Field        | Description                                                       |
| ------------ | ----------------------------------------------------------------- |
| `kode_murid` | Auto-generated if not provided (format: DDMMYY + 4 random digits) |
| `user_id`    | Auto-created if email is provided                                 |

---

## Example Requests

### Minimal Request (Only Required Field)

```json
{
    "nama_lengkap": "I Putu Bagus Dharma Putra"
}
```

### Complete Request (All Fields)

```json
{
    "nama_lengkap": "I Putu Bagus Dharma Putra",
    "kode_murid": "0602261859",
    "jenis_kelamin": "L",
    "tanggal_lahir": "2010-05-15",
    "no_hp": null,
    "email": "bagusdharma@gmail.com",
    "alamat": "Br Tunjuk Tengah",
    "jenjang_id": 2,
    "sekolah_asal": "SD Immaculata",
    "kelas_sekolah": "1",
    "nama_wali": "Orang Tua Bagus",
    "no_hp_wali": "081238563705",
    "email_wali": "wali.bagus@gmail.com",
    "hubungan_wali": "Orang Tua",
    "catatan_khusus": null,
    "kebutuhan_khusus": null,
    "status": "Aktif",
    "password": "SecurePass123"
}
```

### Typical Request (Common Fields)

```json
{
    "nama_lengkap": "Ni Kadek Ayu Lestari",
    "jenis_kelamin": "P",
    "tanggal_lahir": "2012-08-20",
    "email": "ayulestari@gmail.com",
    "alamat": "Jl. Raya Ubud No. 45",
    "jenjang_id": 1,
    "sekolah_asal": "SD Negeri 1 Ubud",
    "kelas_sekolah": "3",
    "nama_wali": "I Wayan Suarta",
    "no_hp_wali": "081234567890",
    "status": "Aktif",
    "password": "password123"
}
```

---

## Response

### Success Response (201 Created)

```json
{
    "data": {
        "id": 1,
        "kode_murid": "0602261234",
        "nama_lengkap": "I Putu Bagus Dharma Putra",
        "jenis_kelamin": "L",
        "tanggal_lahir": "2010-05-15",
        "no_hp": null,
        "no_hp_display": "081238563705",
        "email": "bagusdharma@gmail.com",
        "alamat": "Br Tunjuk Tengah",
        "jenjang_id": 2,
        "jenjang": {
            "id": 2,
            "nama": "SD",
            "kode": "SD"
        },
        "sekolah_asal": "SD Immaculata",
        "kelas_sekolah": "1",
        "nama_wali": "Orang Tua Bagus",
        "no_hp_wali": "081238563705",
        "email_wali": "wali.bagus@gmail.com",
        "hubungan_wali": "Orang Tua",
        "catatan_khusus": null,
        "kebutuhan_khusus": null,
        "status": "Aktif",
        "created_at": "2026-02-06T10:00:00.000000Z",
        "updated_at": "2026-02-06T10:00:00.000000Z"
    },
    "success": true,
    "message": "Murid berhasil ditambahkan"
}
```

### Error Response (422 Validation Error)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "nama_lengkap": ["The nama lengkap field is required."],
        "email": ["The email must be a valid email address."],
        "jenjang_id": ["The selected jenjang id is invalid."]
    }
}
```

---

## Important Notes

### 📝 Field Behaviors

1. **`kode_murid`**
    - If not provided: Auto-generated (format: `DDMMYY` + 4 random digits)
    - If provided: Must be unique
    - Example: `0602261234` (06 Feb 2026 + random 1234)

2. **`no_hp` & `no_hp_wali`**
    - Both are **nullable**
    - If `no_hp` is empty, `no_hp_display` will show `no_hp_wali`
    - Useful for students who don't have their own phone

3. **`email` & `password`**
    - If `email` is provided, a user account will be created automatically
    - If `password` is not provided, default is `"password"`
    - User will be assigned the "Student" role

4. **`status`**
    - Default: `"Aktif"`
    - Allowed values: `"Aktif"` or `"Non Aktif"`

5. **`jenjang_id`**
    - Must exist in the `jenjang` table
    - Common values: 1 (TK), 2 (SD), 3 (SMP), 4 (SMA)

---

## Validation Rules Summary

| Field              | Required | Min | Max | Format                 |
| ------------------ | -------- | --- | --- | ---------------------- |
| `nama_lengkap`     | ✅       | -   | 255 | String                 |
| `jenis_kelamin`    | ❌       | -   | -   | `L` or `P`             |
| `tanggal_lahir`    | ❌       | -   | -   | Date (YYYY-MM-DD)      |
| `no_hp`            | ❌       | 8   | 20  | String                 |
| `email`            | ❌       | -   | 255 | Valid email            |
| `alamat`           | ❌       | -   | -   | String                 |
| `jenjang_id`       | ❌       | -   | -   | Integer (exists in DB) |
| `sekolah_asal`     | ❌       | -   | 255 | String                 |
| `kelas_sekolah`    | ❌       | -   | 50  | String                 |
| `nama_wali`        | ❌       | -   | 255 | String                 |
| `no_hp_wali`       | ❌       | -   | 20  | String                 |
| `email_wali`       | ❌       | -   | 255 | Valid email            |
| `hubungan_wali`    | ❌       | -   | 50  | String                 |
| `catatan_khusus`   | ❌       | -   | -   | String                 |
| `kebutuhan_khusus` | ❌       | -   | -   | String                 |
| `status`           | ❌       | -   | -   | `Aktif` or `Non Aktif` |
| `password`         | ❌       | 8   | -   | String                 |

---

## Testing with cURL

```bash
curl -X POST https://api.shineeducationbali.com/api/v2/murid \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nama_lengkap": "I Putu Test Student",
    "jenis_kelamin": "L",
    "email": "test@example.com",
    "no_hp_wali": "081234567890",
    "nama_wali": "Orang Tua Test",
    "status": "Aktif",
    "password": "password123"
  }'
```

---

**Last Updated:** 2026-02-06  
**API Version:** v2
