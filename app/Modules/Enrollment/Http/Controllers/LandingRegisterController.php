<?php

namespace App\Modules\Enrollment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Enrollment\Application\Services\EnrollmentService;
use App\Modules\Enrollment\Http\Requests\LandingRegisterRequest;
use App\Modules\Enrollment\Http\Resources\EnrollmentResource;
use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Student\Application\Services\MuridService;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class LandingRegisterController extends Controller
{
    public function __construct(
        protected EnrollmentService $enrollmentService,
        protected MuridService $muridService
    ) {}

    public function store(LandingRegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $muridBaru = $validated['murid_baru'];

        $enrollment = DB::transaction(function () use ($validated, $muridBaru) {
            $email = $muridBaru['email'] ?? $muridBaru['email_wali'] ?? null;
            if (! $email) {
                throw new \InvalidArgumentException('Email murid atau wali wajib diisi.');
            }

            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                throw new \InvalidArgumentException('Email sudah terdaftar. Silakan gunakan email lain atau hubungi admin.');
            }

            $user = User::create([
                'name' => $muridBaru['nama_lengkap'],
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
                'status' => UserStatus::AKTIF,
            ]);

            $studentRole = Role::findByName('Student', 'web');
            $user->assignRole($studentRole);

            $muridData = array_merge($muridBaru, [
                'user_id' => $user->id,
                'jenjang_id' => $validated['jenjang_id'],
            ]);
            $murid = $this->muridService->create($muridData);

            $enrollmentPayload = [
                'murid_id' => $murid->id,
                'program_id' => $validated['program_id'],
                'jenjang_id' => $validated['jenjang_id'],
                'paket_id' => $validated['paket_id'],
                'jumlah_siswa' => $validated['jumlah_siswa'],
                'tanggal_mulai' => $validated['tanggal_mulai'] ?? now()->format('Y-m-d'),
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'biaya_pendaftaran_amount' => $validated['biaya_pendaftaran_amount'] ?? 0,
                'biaya_pendaftaran_status' => $validated['biaya_pendaftaran_status'] ?? 'WAIVED',
                'biaya_pendaftaran_due_date' => $validated['biaya_pendaftaran_due_date'] ?? null,
                'created_by' => null,
            ];

            return $this->enrollmentService->create($enrollmentPayload);
        });

        $enrollment->load(['murid', 'program', 'jenjang', 'paket']);

        return ApiResponse::created(
            new EnrollmentResource($enrollment),
            'Pendaftaran berhasil. Data akan muncul di admin.'
        );
    }
}
