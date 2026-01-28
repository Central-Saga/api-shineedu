<?php

namespace Tests\Unit\Services;

use App\Modules\AcademicSessions\Domain\Models\SesiAbsensiMurid;
use App\Modules\AcademicSessions\Domain\Models\Session;
use App\Modules\Catalog\Domain\Models\Paket;
use App\Modules\Enrollment\Application\Exceptions\InsufficientCreditException;
use App\Modules\Enrollment\Application\Services\SaldoPertemuanService;
use App\Modules\Enrollment\Domain\Models\Enrollment;
use App\Modules\Enrollment\Domain\Models\PaketMurid;
use App\Modules\Enrollment\Domain\Models\PaketMuridLedger;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaldoPertemuanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $user;
    protected $enrollment;
    protected $paket;
    protected $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SaldoPertemuanService();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create dependencies
        $program = \App\Modules\Catalog\Domain\Models\Program::create([
            'kode' => 'PRG01',
            'nama' => 'Program Test',
            'status' => 'Aktif'
        ]);

        $jenjang = \App\Modules\Catalog\Domain\Models\Jenjang::create([
            'kode' => 'JNJ01',
            'nama' => 'Jenjang Test',
            'status' => 'Aktif'
        ]);

        $murid = \App\Modules\Student\Domain\Models\Murid::create([
            'kode_murid' => 'MRD01',
            'nama_lengkap' => 'Murid Test',
            'status' => 'Aktif',
            'tanggal_lahir' => '2010-01-01',
            'jenis_kelamin' => 'L'
        ]);

        $this->kelas = \App\Modules\Academic\Domain\Models\Kelas::create([
            'kode_kelas' => 'KLS01',
            'nama_kelas' => 'Kelas Test',
            'program_id' => $program->id,
            'jenjang_id' => $jenjang->id,
            'tipe_kelas' => 'REGULER',
            'status' => 'Aktif',
            'periode_mulai' => now(),
            'periode_selesai' => now()->addYear(),
        ]);

        // Setup data
        $this->paket = Paket::create([
            'kode' => 'PKT01',
            'nama' => 'Paket Reguler',
            'tipe' => 'REGULER',
            'pertemuan_per_bulan' => 4,
            'durasi_menit' => 60,
        ]);

        $this->enrollment = Enrollment::create([
            'murid_id' => $murid->id,
            'program_id' => $program->id,
            'jenjang_id' => $jenjang->id,
            'paket_id' => $this->paket->id,
            'harga_final' => 500000,
            'status' => 'Aktif',
            'tanggal_mulai' => now(),
        ]);
    }

    public function test_create_paket_murid_creates_topup_ledger()
    {
        $paketMurid = $this->service->createPaketMurid($this->enrollment, $this->paket->id);

        $this->assertDatabaseHas('paket_murid', [
            'id' => $paketMurid->id,
            'enrollment_id' => $this->enrollment->id,
            'paket_id' => $this->paket->id,
        ]);

        $this->assertDatabaseHas('paket_murid_ledger', [
            'paket_murid_id' => $paketMurid->id,
            'type' => 'TOPUP',
            'qty' => 4,
            'reference_type' => 'purchase',
        ]);

        $this->assertEquals(4, $paketMurid->saldo_current);
    }

    public function test_deduct_credit_for_attendance_reduces_saldo()
    {
        // Setup package with credit
        $paketMurid = $this->service->createPaketMurid($this->enrollment, $this->paket->id);

        // Mock attendance using proper Kelas
        $session = Session::create([
            'kelas_id' => $this->kelas->id,
            'tanggal' => now(),
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '11:00',
            'status' => 'SCHEDULED'
        ]);

        $absensi = SesiAbsensiMurid::create([
            'realisasi_jadwal_kerja_id' => $session->id,
            'enrollment_id' => $this->enrollment->id,
            'status' => 'HADIR',
        ]);

        // Deduct
        $this->service->deductCreditForAttendance($absensi);

        $paketMurid->refresh();
        $this->assertEquals(3, $paketMurid->saldo_current);

        $this->assertDatabaseHas('paket_murid_ledger', [
            'paket_murid_id' => $paketMurid->id,
            'type' => 'USE',
            'qty' => -1,
            'reference_type' => 'attendance',
            'reference_id' => $absensi->id,
        ]);
    }

    public function test_deduct_credit_is_idempotent()
    {
        // Setup
        $paketMurid = $this->service->createPaketMurid($this->enrollment, $this->paket->id);

        $session = Session::create([
            'kelas_id' => $this->kelas->id,
            'tanggal' => now(),
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '11:00',
            'status' => 'SCHEDULED'
        ]);

        $absensi = SesiAbsensiMurid::create([
            'realisasi_jadwal_kerja_id' => $session->id,
            'enrollment_id' => $this->enrollment->id,
            'status' => 'HADIR'
        ]);

        // Deduct twice
        $this->service->deductCreditForAttendance($absensi);
        $this->service->deductCreditForAttendance($absensi);

        $paketMurid->refresh();
        $this->assertEquals(3, $paketMurid->saldo_current); // Should be 3, not 2

        // Should only be 1 USE record
        $this->assertCount(1, PaketMuridLedger::where('type', 'USE')->where('reference_id', $absensi->id)->get());
    }

    public function test_deduct_credit_throws_exception_when_saldo_zero()
    {
        // Create empty package manually
        $paketMurid = PaketMurid::create([
            'enrollment_id' => $this->enrollment->id,
            'paket_id' => $this->paket->id,
            'status' => 'AKTIF',
        ]);
        // Saldo is 0

        $session = Session::create([
            'kelas_id' => $this->kelas->id,
            'tanggal' => now(),
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '11:00',
            'status' => 'SCHEDULED'
        ]);

        $absensi = SesiAbsensiMurid::create([
            'realisasi_jadwal_kerja_id' => $session->id,
            'enrollment_id' => $this->enrollment->id,
            'status' => 'HADIR'
        ]);

        $this->expectException(InsufficientCreditException::class);

        $this->service->deductCreditForAttendance($absensi);
    }

    public function test_refund_credit_when_hadir_cancelled()
    {
        // Setup
        $paketMurid = $this->service->createPaketMurid($this->enrollment, $this->paket->id);

        $session = Session::create([
            'kelas_id' => $this->kelas->id,
            'tanggal' => now(),
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '11:00',
            'status' => 'SCHEDULED'
        ]);

        $absensi = SesiAbsensiMurid::create([
            'realisasi_jadwal_kerja_id' => $session->id,
            'enrollment_id' => $this->enrollment->id,
            'status' => 'HADIR'
        ]);

        // Deduct first
        $this->service->deductCreditForAttendance($absensi);
        $this->assertEquals(3, $paketMurid->refresh()->saldo_current);

        // Refund
        $this->service->refundCreditForAttendance($absensi);

        $this->assertEquals(4, $paketMurid->refresh()->saldo_current);

        $this->assertDatabaseHas('paket_murid_ledger', [
            'type' => 'ADJUST',
            'qty' => 1,
            'reference_type' => 'attendance_rollback',
            'reference_id' => $absensi->id,
        ]);
    }

    public function test_select_package_fifo()
    {
        // Create OLD package (2025)
        $oldPaket = PaketMurid::create([
            'enrollment_id' => $this->enrollment->id,
            'paket_id' => $this->paket->id,
            'status' => 'AKTIF',
            'tanggal_mulai' => '2025-01-01',
        ]);
        // Add balance to old
        PaketMuridLedger::create([
            'paket_murid_id' => $oldPaket->id,
            'type' => 'TOPUP',
            'qty' => 2,
            'tanggal' => now()
        ]);

        // Create NEW package (2026)
        $newPaket = PaketMurid::create([
            'enrollment_id' => $this->enrollment->id,
            'paket_id' => $this->paket->id,
            'status' => 'AKTIF',
            'tanggal_mulai' => '2026-01-01',
        ]);
        // Add balance to new
        PaketMuridLedger::create([
            'paket_murid_id' => $newPaket->id,
            'type' => 'TOPUP',
            'qty' => 4,
            'tanggal' => now()
        ]);

        // Test selection
        $selected = $this->service->selectPackageForDeduction($this->enrollment);

        $this->assertEquals($oldPaket->id, $selected->id);
    }
}
