# Job Application API – Clean Structure (api-shineedu)

Struktur API untuk **Job Applications** mengikuti pola modul HR yang ada (Cuti, Employees).

---

## 1. Database

### Migration

- **`database/migrations/2026_01_30_100000_create_job_vacancies_table.php`**
    - `id`, `title`, `location`, `is_active`, `timestamps`

- **`database/migrations/2026_01_30_100001_create_job_applications_table.php`**
    - `id`, `job_vacancy_id` (FK), `first_name`, `last_name`, `email`, `phone`, `experience`, `education`, `address`, `status`, `tracking_code`, `timestamps`, `soft_deletes`
    - File CV/surat lamaran disimpan via **Spatie Media Library** (collection `resume`, `cover_letter`)

### Seeder

- **`JobVacancySeeder`** – mengisi posisi awal (selaras dengan landing): Guru Bahasa Inggris, Staff Administrasi, dll.
- Dipanggil dari `DatabaseSeeder`.

---

## 2. Module HR – Job Application

### Domain

- **`app/Modules/HR/Domain/Models/JobVacancy.php`**
    - Fillable: title, location, is_active
    - Relation: `jobApplications()`

- **`app/Modules/HR/Domain/Models/JobApplication.php`**
    - Fillable: job_vacancy_id, first_name, last_name, email, phone, experience, education, address, status, tracking_code
    - Implements `HasMedia` (Spatie): collections `resume`, `cover_letter`
    - SoftDeletes
    - Relation: `jobVacancy()`

### Application

- **`app/Modules/HR/Application/Services/JobApplicationService.php`**
    - `getList(array $params)` – paginated, filter q, position_id, status, sort
    - `create(array $data)` – generate `tracking_code` (JA-XXXXXXXX), simpan media
    - `update(JobApplication, array $data)` – update + replace file jika ada
    - `delete(JobApplication)` – soft delete

### Http

- **Controllers**
    - **`JobApplicationController`** (V2): index, store, show, update, destroy
    - **`JobVacancyController`** (V2): index (public, daftar posisi aktif)

- **Requests**
    - **`StoreJobApplicationRequest`**: first_name, last_name, email, phone, position_id/job_vacancy_id, experience, education, address, resume (required), cover_letter (optional)
    - **`UpdateJobApplicationRequest`**: semua field optional + status

- **Resources**
    - **`JobApplicationResource`**: id, position_id, first_name, last_name, email, phone, experience, education, address, resume_url, cover_letter_url, status, tracking_code, created_at, updated_at, position { id, title, location }

---

## 3. Routes (API v2)

### Public (tanpa auth)

- `GET /api/v2/public/job-vacancies` – daftar posisi aktif (untuk dropdown landing)
- `POST /api/v2/public/job-applications` – kirim lamaran (landing form)

### Protected (auth:sanctum + permission)

- `GET /api/v2/job-applications` – list (permission: `job_application.view`)
- `POST /api/v2/job-applications` – create (permission: `job_application.create`)
- `GET /api/v2/job-applications/{job_application}` – detail (`job_application.view`)
- `PUT|PATCH /api/v2/job-applications/{job_application}` – update (`job_application.update`)
- `DELETE /api/v2/job-applications/{job_application}` – delete (`job_application.delete`)

---

## 4. Permissions

- Di **`RoleAndPermissionSeeder`** modul `job_application` sudah ditambahkan.
- Permission yang dibuat: `job_application.view`, `job_application.create`, `job_application.update`, `job_application.delete`, `job_application.manage`.
- Setelah `php artisan db:seed --class=RoleAndPermissionSeeder` (atau full seed), assign ke role Admin/Superadmin agar CRUD di app-shineedu berfungsi.

---

## 5. Konsistensi dengan app-shineedu & landing

- **app-shineedu** repository memanggil: `GET/POST job-applications`, `GET/PUT/DELETE job-applications/{id}`. Base URL: `NEXT_PUBLIC_API_BASE_URL/api/v2`.
- **landing-shineedu** ke depan bisa: `GET .../public/job-vacancies` untuk dropdown posisi, `POST .../public/job-applications` (multipart) untuk kirim lamaran.
- Response format: `{ success, message, data, meta? }` (ApiResponse). List: `data` array + `meta` (current_page, per_page, total, last_page, from, to).
